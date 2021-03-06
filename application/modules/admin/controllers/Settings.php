<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Settings extends CI_Controller {
    public function __construct()
    {
        parent::__construct();

        verify_session('admin');

        $this->load->library('form_validation');
        $this->load->model('setting_model', 'setting');
    }

    public function index()
    {
        $params['title'] = 'Pengaturan';

        $settings['flash'] = $this->session->flashdata('settings_flash');

        $this->load->view('header', $params);
        $this->load->view('settings/settings', $settings);
        $this->load->view('footer');
    }

    public function update()
    {
        $fields = array(
            'store_name', 'store_phone_number', 'store_email', 'store_tagline', 'store_description',
            'store_address', 'min_shop_to_free_shipping_cost', 'shipping_cost'
        );

        foreach ($fields as $field)
        {
            $data = $this->input->post($field);

            update_settings($field, $data);
        }

        $this->session->set_flashdata('settings_flash', 'Pengaturan berhasil diperbarui');
        redirect('admin/settings');
    }

    public function profile()
    {
        $params['title'] = 'Profil Saya';

        $profile['flash'] = $this->session->flashdata('settings_flash');
        $profile['user'] = $this->setting->get_profile();

        $this->load->view('header', $params);
        $this->load->view('settings/profile', $profile);
        $this->load->view('footer');
    }

    public function profile_update()
    {
        $this->form_validation->set_error_delimiters('<div class="font-weight-bold text-danger">', '</div>');

        $this->form_validation->set_rules('name', 'Nama lengkap', 'required');
        $this->form_validation->set_rules('email', 'Email', 'required');
        $this->form_validation->set_rules('username', 'required');

        if ($this->form_validation->run() === FALSE)
        {
            $this->profile();
        }
        else
        {
            $data = $this->setting->get_profile();
            $current_profile_picture = $data->profile_picture;

            $name = $this->input->post('name');
            $email = $this->input->post('email');
            $username = $this->input->post('username');
            $password = $this->input->post('password');

            if ($password)
                $password = password_hash($password, PASSWORD_BCRYPT);

            $config['upload_path'] = './assets/uploads/users/';
            $config['allowed_types'] = 'jpg|png|jpeg';
            $config['max_size'] = 2048;

            $this->load->library('upload', $config);

            if ( isset($_FILES['picture']) && @$_FILES['picture']['error'] == '0')
            {
                if ( $this->upload->do_upload('picture'))
                {
                    $upload_data = $this->upload->data();
                    $new_file_name = $upload_data['file_name'];

                    $profile_picture = $new_file_name;

                    if ( file_exists('assets/uploads/users/'. $current_profile_picture))
                        unlink('./assets/uploads/users/'. $current_profile_picture);
                }
            }
            else
            {
                $profile_picture = $current_profile_picture;
            }

            $data = array(
                'name' => $name,
                'email' => $email,
                'username' => $username,
                'password' => $password,
                'profile_picture' => $profile_picture
            );

            $this->setting->update_profile($data);

            $this->session->set_flashdata('settings_flash', 'Profil berhasil diperbarui');
            redirect('admin/settings/profile');
        }
    }
}