<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class My_Controller extends CI_Controller
{
    public $provider;

    public function __construct()
    {
        parent::__construct();

        $this->load->library(['form_validation', 'session']);
        $this->load->helper(['url', 'form']);
        $this->load->model('general_model');

        $this->admin = $this->session->userdata('admin');

        // echo "<pre>";
        // print_r($this->admin);
        // die;
        if (!$this->admin) {
            redirect('login');
        }
    }
}

