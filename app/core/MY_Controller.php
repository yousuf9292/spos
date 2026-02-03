<?php defined('BASEPATH') OR exit('No direct script access allowed');

class MY_Controller extends CI_Controller {

    function __construct() {
        parent::__construct();

        // UI ONLY MODE (no database)
        // Set Render env var: UI_ONLY=1
        $ui_only = getenv('UI_ONLY') === '1';

        if ($ui_only) {
            // Minimal defaults so views can render without DB
            $this->Settings = (object) array(
                'language' => 'english',
                'selected_language' => 'english',
                'pin_code' => NULL,
                'theme' => 'default',
            );

            // Language handling (cookie override still works)
            if ($spos_language = $this->input->cookie('spos_language', TRUE)) {
                $this->Settings->selected_language = $spos_language;
                $this->config->set_item('language', $spos_language);
                $this->lang->load('app', $spos_language);
            } else {
                $this->config->set_item('language', $this->Settings->language);
                $this->lang->load('app', $this->Settings->language);
            }

            $this->theme = $this->Settings->theme.'/views/';
            $this->data['assets']   = base_url() . 'themes/default/assets/';
            $this->data['Settings'] = $this->Settings;

            // Auth / store / categories are DB-backed, so keep empty
            $this->loggedIn = FALSE;
            $this->data['loggedIn'] = $this->loggedIn;

            $this->data['store'] = NULL;
            $this->data['categories'] = array();

            $this->Admin = NULL;
            $this->data['Admin'] = $this->Admin;

            $this->m = strtolower($this->router->fetch_class());
            $this->v = strtolower($this->router->fetch_method());
            $this->data['m'] = $this->m;
            $this->data['v'] = $this->v;

            return; // IMPORTANT: stop here, skip DB calls below
        }

        // ===== Normal mode (with database) =====
        $this->Settings = $this->site->getSettings();
        if($spos_language = $this->input->cookie('spos_language', TRUE)) {
            $this->Settings->selected_language = $spos_language;
            $this->config->set_item('language', $spos_language);
            $this->lang->load('app', $spos_language);
        } else {
            $this->Settings->selected_language = $this->Settings->language;
            $this->config->set_item('language', $this->Settings->language);
            $this->lang->load('app', $this->Settings->language);
        }
        $this->Settings->pin_code = $this->Settings->pin_code ? md5($this->Settings->pin_code) : NULL;
        $this->theme = $this->Settings->theme.'/views/';
        $this->data['assets'] = base_url() . 'themes/default/assets/';
        $this->data['Settings'] = $this->Settings;
        $this->loggedIn = $this->tec->logged_in();
        $this->data['loggedIn'] = $this->loggedIn;
        $this->data['store'] = $this->site->getStoreByID($this->session->userdata('store_id'));
        $this->data['categories'] = $this->site->getAllCategories();
        $this->Admin = $this->tec->in_group('admin') ? TRUE : NULL;
        $this->data['Admin'] = $this->Admin;
        $this->m = strtolower($this->router->fetch_class());
        $this->v = strtolower($this->router->fetch_method());
        $this->data['m']= $this->m;
        $this->data['v'] = $this->v;
    }

    function page_construct($page, $data = array(), $meta = array()) {

        // UI ONLY MODE: don't call DB-backed methods here either
        $ui_only = getenv('UI_ONLY') === '1';

        if(empty($meta)) { $meta['page_title'] = isset($data['page_title']) ? $data['page_title'] : ''; }
        $meta['message'] = isset($data['message']) ? $data['message'] : $this->session->flashdata('message');
        $meta['error'] = isset($data['error']) ? $data['error'] : $this->session->flashdata('error');
        $meta['warning'] = isset($data['warning']) ? $data['warning'] : $this->session->flashdata('warning');
        $meta['ip_address'] = $this->input->ip_address();
        $meta['Admin'] = isset($data['Admin']) ? $data['Admin'] : NULL;
        $meta['loggedIn'] = isset($data['loggedIn']) ? $data['loggedIn'] : FALSE;
        $meta['Settings'] = isset($data['Settings']) ? $data['Settings'] : NULL;
        $meta['assets'] = isset($data['assets']) ? $data['assets'] : base_url() . 'themes/default/assets/';
        $meta['store'] = isset($data['store']) ? $data['store'] : NULL;

        if ($ui_only) {
            $meta['suspended_sales'] = array();
            $meta['qty_alert_num'] = 0;
        } else {
            $meta['suspended_sales'] = $this->site->getUserSuspenedSales();
            $meta['qty_alert_num'] = $this->site->getQtyAlerts();
        }

        $this->load->view($this->theme . 'header', $meta);
        $this->load->view($this->theme . $page, $data);
        $this->load->view($this->theme . 'footer');
    }
}
