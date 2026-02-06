<?php
require_once(APPPATH . 'core/My_Controller.php');
class Dashboard extends My_Controller

{



    public function __construct()

    {

        parent::__construct();

      

    }





   public function index()
{
    $admin_id = $this->admin['user_id'];

    // Current counts
    $data['sites_count'] = $this->general_model->getCount('sites', [
        'isActive' => 1, 'admin_id' => $admin_id
    ]);

    $data['plots_count'] = $this->general_model->getCount('plots', [
        'isActive' => 1, 'admin_id' => $admin_id
    ]);

    $data['users_count'] = $this->general_model->getCount('users', [
        'isActive' => 1, 'admin_id' => $admin_id
    ]);

    $data['Inquiry_count'] = $this->general_model->getCount('inquiries', [
        'isActive' => 1, 'admin_id' => $admin_id
    ]);


    // 🔥 GET LAST WEEK COUNTS
    $data['sites_last_week'] = $this->general_model->getCount('sites', [
        'admin_id' => $admin_id,
        'isActive' => 1,
        'created_at >=' => date('Y-m-d', strtotime("-7 days"))
    ]);

    $data['plots_last_week'] = $this->general_model->getCount('plots', [
        'admin_id' => $admin_id,
        'isActive' => 1,
        'created_at >=' => date('Y-m-d', strtotime("-7 days"))
    ]);

    $data['users_last_week'] = $this->general_model->getCount('users', [
        'admin_id' => $admin_id,
        'isActive' => 1,
        'created_at >=' => date('Y-m-d', strtotime("-7 days"))
    ]);

    $data['inquiry_last_week'] = $this->general_model->getCount('inquiries', [
        'admin_id' => $admin_id,
        'isActive' => 1,
        'created_at >=' => date('Y-m-d', strtotime("-7 days"))
    ]);
// -----------------
// -------------
// EXPENSE SUMMARY (CURRENT MONTH)
// ------------------------------
$current_month = date('m');
$current_year  = date('Y');
$this->db->select_sum('amount');
$this->db->where('admin_id', $admin_id);
$this->db->where('isActive', 1);
$this->db->where('MONTH(date)', $current_month);
$this->db->where('YEAR(date)', $current_year);
$data['total_expense'] = (float)$this->db->get('expenses')->row()->amount;

// APPROVED EXPENSE
$this->db->select_sum('amount');
$this->db->where('admin_id', $admin_id);
$this->db->where('status', 'approve');
$this->db->where('MONTH(date)', $current_month);
$this->db->where('YEAR(date)', $current_year);
$data['approved_expense'] = (float)$this->db->get('expenses')->row()->amount;

// PENDING EXPENSE
$this->db->select_sum('amount');
$this->db->where('admin_id', $admin_id);
$this->db->where('status', 'pending');
$this->db->where('MONTH(date)', $current_month);
$this->db->where('YEAR(date)', $current_year);
$data['pending_expense'] = (float)$this->db->get('expenses')->row()->amount;

$this->db->where('admin_id', $admin_id);
$this->db->where('status', 'pending');
$this->db->where('MONTH(attendance_time)', $current_month);
$this->db->where('YEAR(attendance_time)', $current_year);
$data['attendance_pending'] = $this->db->count_all_results('attendance');
$this->db->where('admin_id', $admin_id);
$this->db->where('MONTH(attendance_time)', $current_month);
$this->db->where('YEAR(attendance_time)', $current_year);
$data['attendance_total'] = $this->db->count_all_results('attendance');

// ⭐ 2. Total Approved (present + approve + rejected)
$this->db->where('admin_id', $admin_id);
$this->db->where_in('status', ['present', 'absent', 'rejected']);
$this->db->where('MONTH(attendance_time)', $current_month);
$this->db->where('YEAR(attendance_time)', $current_year);
$data['attendance_approved'] = $this->db->count_all_results('attendance');
    // Load views
    $this->load->view('header');
    $this->load->view('dashboard_view', $data);
    $this->load->view('footer');
}



public function inquiry(){
    $this->load->view('header');
    $this->load->view('inquiry_view');
    $this->load->view('footer');
}

public function fetch_inquiries()
{
    header('Content-Type: application/json');

    $page   = intval($this->input->post("page")) ?: 1;
    $search = trim($this->input->post("search"));
    $limit  = 10;
    $offset = ($page - 1) * $limit;

    $admin_id = $this->admin['user_id'];

    $this->db->select("inquiries.*, 
                       users.name AS user_name,
                       sites.name,
                       plots.plot_number");
    $this->db->from("inquiries");
    $this->db->join("users", "users.id = inquiries.user_id", "left");
    $this->db->join("sites", "sites.id = inquiries.site_id", "left");
    $this->db->join("plots", "plots.id = inquiries.plot_id", "left");

    $this->db->where("inquiries.admin_id", $admin_id);
    $this->db->where("inquiries.isActive", 1); // <-- Only active inquiries

    if (!empty($search)) {
        $this->db->group_start();
        $this->db->like("users.name", $search);
        $this->db->or_like("sites.site_name", $search);
        $this->db->or_like("plots.plot_number", $search);
        $this->db->or_like("inquiries.customer_name", $search);
        $this->db->group_end();
    }

    $total = $this->db->count_all_results("", FALSE);

    $this->db->limit($limit, $offset);

    $data = $this->db->get()->result();

    echo json_encode([
        "status" => true,
        "data"   => $data,
        "total"  => $total,
        "limit"  => $limit,
        "page"   => $page
    ]);
}
public function attedance(){
    $this->load->view('header');
    $this->load->view('attedance_view');
    $this->load->view('footer');
}

   public function delete_inquiry()
{
    $id = $this->input->post('id');

    if (!$id) {
        echo json_encode([
            "status" => false,
            "message" => "Invalid Inquiry ID"
        ]);
        return;
    }

    // Soft delete (Update isActive = 0)
    $update = $this->db->where("id", $id)->update("inquiries", [
        "isActive" => 0
    ]);

    if ($update) {
        echo json_encode([
            "status" => true,
            "message" => "Inquiry deleted successfully"
        ]);
    } else {
        echo json_encode([
            "status" => false,
            "message" => "Failed to delete inquiry"
        ]);
    }
}
public function fetch_attendance()
{
    header('Content-Type: application/json');

    $page   = intval($this->input->post("page")) ?: 1;
    $search = trim($this->input->post("search"));
    $limit  = 10;
    $offset = ($page - 1) * $limit;

    $admin_id = $this->admin['user_id'];

    $this->db->select("attendance.*, users.name AS user_name, users.mobile");
    $this->db->from("attendance");
    $this->db->join("users", "users.id = attendance.user_id", "left");
    $this->db->where("attendance.admin_id", $admin_id);
    $this->db->where("attendance.isActive", 1);

    if (!empty($search)) {
        $this->db->group_start();
        $this->db->like("users.name", $search);
        $this->db->or_like("users.mobile", $search);
        $this->db->group_end();
    }

    $total = $this->db->count_all_results("", FALSE);
    $this->db->limit($limit, $offset);
    $data = $this->db->get()->result();

    echo json_encode([
        "status" => true,
        "data"   => $data,
        "total"  => $total,
        "limit"  => $limit,
        "page"   => $page
    ]);
}
public function delete_attendance()
{
    $id = $this->input->post("id");

    $this->db->where("id", $id);
    $this->db->update("attendance", ["isActive" => 0]);

    echo json_encode(["status" => true]);
}
public function update_status()
{
    $id = $this->input->post('id');
    $status = $this->input->post('status');

    // Validate inputs
    if (!$id || !$status) {
        echo json_encode([
            "status" => false,
            "message" => "Invalid request"
        ]);
        return;
    }

    // Custom SQL Query
    $sql = "UPDATE attendance SET status = ? WHERE id = ?";
    $updated = $this->db->query($sql, array($status, $id));

    if ($updated) {
        echo json_encode([
            "status" => true,
            "message" => "Attendance status updated successfully!"
        ]);
    } else {
        echo json_encode([
            "status" => false,
            "message" => "Failed to update status."
        ]);
    }
}


}  

?>