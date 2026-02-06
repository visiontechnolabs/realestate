<?php
require_once(APPPATH . 'core/My_Controller.php');
class Site extends My_Controller

{



    public function __construct()

    {

        parent::__construct();

      

    }





    public function index(){

        $this->load->view('header');

        $this->load->view('site_view');

        $this->load->view('footer');

    }
public function get_sites()
{
    header('Content-Type: application/json');

    $limit  = 10;
    $page   = (int)$this->input->get('page');
    $search = trim($this->input->get('search'));

    if ($page < 1) $page = 1;
    $offset = ($page - 1) * $limit;

    // Logged-in Admin ID
    $admin_id = $this->admin['user_id'];

    // ------------------------------
    // BASE QUERY FOR SITES
    // ------------------------------
    $this->db->from('sites');
    $this->db->where('isActive', 1);
    $this->db->where('admin_id', $admin_id);

    if (!empty($search)) {
        $this->db->group_start()
                 ->like('name', $search)
                 ->or_like('location', $search)
                 ->group_end();
    }

    // COUNT RESULTS
    $total_records = $this->db->count_all_results('', FALSE);

    // ORDER & LIMIT
    $this->db->order_by('id', 'DESC');
    $this->db->limit($limit, $offset);

    // FETCH SITES
    $sites = $this->db->get()->result();

    // Add plot counts + SITE-WISE EXPENSE LOGIC
    foreach ($sites as &$site) {

        // Total Plots
        $site->total_plots = $this->db
            ->where('site_id', $site->id)
            ->where('admin_id', $admin_id)
            ->where('isActive', 1)
            ->count_all_results('plots');

        // ----------------------------------
        // ⭐ SITE-WISE APPROVED EXPENSES
        // ----------------------------------
        $expense = $this->db
            ->select("SUM(amount) AS site_expense")
            ->from("expenses")
            ->where("admin_id", $admin_id)
            ->where("site_id", $site->id)
            ->where("status", "approve")
            ->get()
            ->row();

        $site->total_expenses = (float)($expense->site_expense ?? 0);
    }

    // ------------------------------
    // ⭐ TOTAL APPROVED EXPENSES (ALL SITES)
    // ------------------------------
    $expense_row = $this->db
        ->select("SUM(amount) AS total_expense")
        ->from("expenses")
        ->where("admin_id", $admin_id)
        ->where("status", "approve")
        ->get()
        ->row();

    $total_expenses = $expense_row->total_expense ?? 0;

    // Pagination
    $total_pages = ceil($total_records / $limit);

    // RESPONSE
    echo json_encode([
        'status'  => true,
        'code'    => 200,
        'message' => 'Sites fetched successfully',
        'data'    => $sites,
        'total_expenses' => (float)$total_expenses,   // TOTAL (all sites)

        'pagination' => [
            'current_page'  => $page,
            'total_pages'   => $total_pages,
            'total_records' => $total_records
        ]
    ]);
}



public function expenses($id) {
    $site_id = $id;

    $data['site_id'] = $site_id; // ← Pass to view

    $this->load->view('header');
    $this->load->view('expenses_view', $data); // ← Send it here
    $this->load->view('footer');
}

  public function get_expenses()
{
    $admin_id = $this->admin['user_id'];

    $page   = $this->input->post('page') ?? 1;
    $limit  = 10;
    $offset = ($page - 1) * $limit;

    $search  = $this->input->post('search');
    $site_id = $this->input->post('site_id');

    /* ----------------------------------------------
       COUNT QUERY
    ---------------------------------------------- */
    $this->db->from('expenses');
    $this->db->where('admin_id', $admin_id);
    $this->db->where('isActive', 1);  // Only active

    if (!empty($site_id)) {
        $this->db->where('site_id', $site_id);
    }

    if (!empty($search)) {
        $this->db->like('description', $search);
    }

    $total = $this->db->count_all_results();


    /* ----------------------------------------------
       DATA QUERY
    ---------------------------------------------- */
    $this->db->select('
        expenses.*, 
        sites.name AS site_name,
        users.name AS user_name
    ');
    $this->db->from('expenses');

    // joins
    $this->db->join('sites', 'sites.id = expenses.site_id', 'left');
    $this->db->join('users', 'users.id = expenses.user_id', 'left');

    // filters
    $this->db->where('expenses.admin_id', $admin_id);
    $this->db->where('expenses.isActive', 1); // Important fix

    if (!empty($site_id)) {
        $this->db->where('expenses.site_id', $site_id);
    }

    if (!empty($search)) {
        $this->db->like('expenses.description', $search);
    }

    $this->db->order_by('expenses.id', 'DESC');
    $this->db->limit($limit, $offset);

    $records = $this->db->get()->result();

    /* ----------------------------------------------
       RESPONSE
    ---------------------------------------------- */
    echo json_encode([
        "status"  => true,
        "records" => $records,
        "total"   => $total,
        "limit"   => $limit,
        "page"    => $page
    ]);
}



    // ➤ DELETE EXPENSE
   public function delete($id) {
    $this->db->where('id', $id)->update('expenses', [
        "isActive" => 0
    ]);

    echo json_encode(["status" => true]);
}


    // ➤ UPDATE STATUS
    public function update_status() {
    $id = $this->input->post('id');
    $status = $this->input->post('status');

    $this->db->where('id', $id)->update('expenses', [
        "status" => $status
    ]);

    echo json_encode(["status" => true]);
}

 public function get_users()
{
    header('Content-Type: application/json');
    $users = $this->db->select('id, name')->from('users')->where('isActive', 1)->get()->result();
    echo json_encode(['status' => true, 'data' => $users]);
}
public function assign_site()
{
    header('Content-Type: application/json');
    $site_id = $this->input->post('site_id');
    $user_id = $this->input->post('user_id');
    $admin_id = $this->input->post('admin_id'); 

    if (!$site_id || !$user_id) {
        echo json_encode(['status' => false, 'message' => 'Missing parameters']);
        return;
    }

    // ✅ Fetch admin_id if not provided in POST
    if (empty($admin_id)) {
        $site = $this->db->select('admin_id')->where('id', $site_id)->get('sites')->row();
        $admin_id = $site ? $site->admin_id : null;
    }

    if (!$admin_id) {
        echo json_encode(['status' => false, 'message' => 'Admin not found for this site']);
        return;
    }

    // ✅ Check if already assigned
    $exists = $this->db->where('site_id', $site_id)
                       ->where('user_id', $user_id)
                       ->get('site_assignments')
                       ->row();

    if ($exists) {
        echo json_encode(['status' => false, 'message' => 'Already assigned']);
        return;
    }

    // ✅ Insert assignment
    $this->db->insert('site_assignments', [
        'site_id'  => $site_id,
        'user_id'  => $user_id,
        'admin_id' => $admin_id,
        'assigned_at' => date('Y-m-d H:i:s')
    ]);

    echo json_encode(['status' => true, 'message' => 'Site assigned successfully']);
}





    public function add_site(){
         $this->load->view('header');

        $this->load->view('site_form');

        $this->load->view('footer');
    }

   public function save_site()
{
    header('Content-Type: application/json');
    $response = ['status' => 'error', 'message' => 'Something went wrong'];

    // ✅ Check if admin is logged in
    $admin_id = $this->admin['user_id'];
    if (!$admin_id) {
        $response['message'] = 'Unauthorized access';
        echo json_encode($response);
        return;
    }

    // ✅ Get POST data
    $site_name   = trim($this->input->post('site_name'));
    $location    = trim($this->input->post('location'));
    $area        = trim($this->input->post('area'));
    $total_plots = trim($this->input->post('total_plots'));

    // ✅ Validation
    if (empty($site_name) || empty($location) || empty($area) || empty($total_plots)) {
        $response['message'] = 'All fields are required';
        echo json_encode($response);
        return;
    }

    // ✅ Check if site already exists (optional duplicate check)
    $exists = $this->general_model->getOne('sites', ['name' => $site_name, 'location' => $location]);
    if ($exists) {
        $response['message'] = 'Site with this name and location already exists';
        echo json_encode($response);
        return;
    }

    // ✅ Prepare data
    $data = [
        'admin_id'   => $admin_id,
        'name'       => $site_name,
        'location'   => $location,
        'area'       => $area,
        'total_plots'=> $total_plots,
        'isActive'   => 1, // Default active
        'created_at' => date('Y-m-d')
    ];

    // ✅ Insert data
    if ($this->general_model->insert('sites', $data)) {
        $response = ['status' => 'success', 'message' => 'Site added successfully'];
    } else {
        $response['message'] = 'Failed to add site';
    }

    echo json_encode($response);
}

public function edit_site($id)
{
    // Fetch site record
    $site = $this->db->where('id', $id)->get('sites')->row();

    // Load view with data
    $this->load->view('header');
    $this->load->view('edit_site_form', ['site' => $site]);
    $this->load->view('footer');
}
public function update_site($id)
{
    $data = [
        'name'        => $this->input->post('site_name'),
        'location'    => $this->input->post('location'),
        'area'        => $this->input->post('area'),
        'total_plots' => $this->input->post('total_plots'),
        'isActive'    => $this->input->post('isActive')
    ];

    $updated = $this->db->where('id', $id)->update('sites', $data);

    if ($updated) {
        echo json_encode(['status' => true, 'message' => 'Site updated successfully']);
    } else {
        echo json_encode(['status' => false, 'message' => 'Failed to update site']);
    }
}


public function delete_site($id = null)
{
    header('Content-Type: application/json');

    // ✅ Check if ID is valid
    if (empty($id) || !is_numeric($id)) {
        echo json_encode([
            'status' => false,
            'code' => 400,
            'message' => 'Invalid or missing site ID'
        ]);
        return;
    }

    // ✅ Check if site exists
    $site = $this->db->where('id', $id)->get('sites')->row();
    if (!$site) {
        echo json_encode([
            'status' => false,
            'code' => 400,
            'message' => 'Site not found'
        ]);
        return;
    }

    // ✅ Soft delete: set isActive = 0
    $this->db->where('id', $id);
    $this->db->update('sites', ['isActive' => 0]);

    if ($this->db->affected_rows() > 0) {
        echo json_encode([
            'status' => true,
            'code' => 200,
            'message' => 'Site deleted successfully'
        ]);
    } else {
        echo json_encode([
            'status' => false,
            'code' => 400,
            'message' => 'Failed to delete site or already inactive'
        ]);
    }
}

public function add_expenses(){
$data['sites'] = $this->general_model->getAll('sites', ['isActive' => '1']);

     $this->load->view('header');
    $this->load->view('add_expenses_form',$data);
    $this->load->view('footer');
}

public function save_exp(){
    header('Content-Type: application/json');
    $response = ['status' => 'error', 'message' => 'Something went wrong'];

    
    $admin_id = $this->admin['user_id'] ?? null;
    if (!$admin_id) {
        $response['message'] = 'Unauthorized access';
        echo json_encode($response);
        return;
    }
   
    $site_id     = $this->input->post('site_id'); 
    $amount = trim($this->input->post('price'));
    $desc = trim($this->input->post('description'));
    $date = trim($this->input->post('date'));

   $data = [
        'admin_id'   => $admin_id,
        'site_id'    => $site_id,
        'amount'=> $amount,
        'description'=> $desc,
        'date'=> $date,
        'status'=> 'approve',
        'isActive'   => 1,
        'created_at' => date('Y-m-d')
    ];

    $insert_id = $this->general_model->insert('expenses', $data);

    if ($insert_id) {
         $response = [
            'status' => 'success',
            'message' => 'expense added successfully',
            
        ];

    } else {
        $response['message'] = 'Failed to add expense';
    }

    echo json_encode($response);
}



   

}  

?>