<?php
class ControllerExtensionModuleZBlackbox extends Controller {
	private $error = array();

	public function index() {

		$this->load->language('extension/module/zblackbox');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('setting/setting');

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {

			$this->model_setting_setting->editSetting('module_zblackbox', $this->request->post);

			$this->session->data['success'] = $this->language->get('text_success');

			$this->response->redirect($this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'], true));
		}

		$data['heading_title'] = $this->language->get('heading_title');
		
		$data['text_edit'] = $this->language->get('text_edit');
		$data['text_enabled'] = $this->language->get('text_enabled');
		$data['text_disabled'] = $this->language->get('text_disabled');
		
		$data['entry_status'] = $this->language->get('entry_status');
		
		$data['text_apikey'] = $this->language->get('text_apikey');

        $data['button_download'] = $this->language->get('button_download');
		
		
		

		$data['button_save'] = $this->language->get('button_save');
		$data['button_cancel'] = $this->language->get('button_cancel');

		if (isset($this->error['warning'])) {
			$data['error_warning'] = $this->error['warning'];
		} else {
			$data['error_warning'] = '';
		}

		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true)
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_module'),
			'href' => $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'], true)
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link('extension/module/zblackbox', 'user_token=' . $this->session->data['user_token'], true)
		);

		$data['action'] = $this->url->link('extension/module/zblackbox', 'user_token=' . $this->session->data['user_token'], true);

		$data['cancel'] = $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'], true);

        $data['downloadLog'] = $this->url->link('extension/module/zblackbox/downloadLog', 'user_token=' . $this->session->data['user_token'], true);


        $data['user_token'] = $this->session->data['user_token'];

		if (isset($this->request->post['module_zblackbox_status'])) {
			$data['module_zblackbox_status'] = $this->request->post['module_zblackbox_status'];
		} else {
			$data['module_zblackbox_status'] = $this->config->get('module_zblackbox_status');
		}
		
		if (isset($this->request->post['module_zblackbox_apikey'])) {
			$data['module_zblackbox_apikey'] = $this->request->post['module_zblackbox_apikey'];
		} else {
			$data['module_zblackbox_apikey'] = $this->config->get('module_zblackbox_apikey');
		}

        if (isset($this->request->post['module_zblackbox_notify_log'])) {
            $data['module_zblackbox_notify_log'] = $this->request->post['module_zblackbox_notify_log'];
        } else {
            $data['module_zblackbox_notify_log'] = $this->config->get('module_zblackbox_notify_log');
        }

        $data['zblackbox_log'] = '';

        $data['zblackbox_log_filname'] = 'zblackbox.log';

        if ($this->config->get('module_zblackbox_notify_log')) {
            $file = DIR_LOGS . $data['zblackbox_log_filname'];

            if (file_exists($file)) {
                $size = filesize($file);

                if ($size >= 5242880) {
                    $suffix = array(
                        'B',
                        'KB',
                        'MB',
                        'GB',
                        'TB',
                        'PB',
                        'EB',
                        'ZB',
                        'YB',
                    );

                    $i = 0;

                    while (($size / 1024) > 1) {
                        $size = $size / 1024;
                        $i++;
                    }

                    $data['error_warning'] = sprintf($this->language->get('error_warning'), basename($file), round(substr($size, 0, strpos($size, '.') + 4), 2) . $suffix[$i]);
                } else {
                    $data['zblackbox_log'] = file_get_contents($file, FILE_USE_INCLUDE_PATH, null);
                }
            }
        } else {
            $this->clearLog();
        }
		
		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('extension/module/zblackbox', $data));
	}

    public function clearLog() {
        $json = array();

        $this->load->language('extension/module/zblackbox');

        if (!$this->user->hasPermission('modify', 'extension/module/zblackbox')) {
            $json['error'] = $this->language->get('error_permission');
        } else {
            $file = DIR_LOGS . 'zblackbox.log';

            $handle = fopen($file, 'w+');

            fclose($handle);

            $json['success'] = $this->language->get('text_success_log');
        }

        $this->response->setOutput(json_encode($json));

    }


    public function downloadLog() {
        $this->load->language('extension/module/zblackbox');

        $file = DIR_LOGS . 'zblackbox.log';

        if (file_exists($file) && filesize($file) > 0) {
            $this->response->addheader('Pragma: public');
            $this->response->addheader('Expires: 0');
            $this->response->addheader('Content-Description: File Transfer');
            $this->response->addheader('Content-Type: application/octet-stream');
            $this->response->addheader('Content-Disposition: attachment; filename="' . $this->config->get('config_name') . '_' . date('Y-m-d_H-i-s', time()) . '_zblackbox.log"');
            $this->response->addheader('Content-Transfer-Encoding: binary');

            $this->response->setOutput(file_get_contents($file, FILE_USE_INCLUDE_PATH, null));
        } else {
            $this->session->data['error'] = sprintf($this->language->get('error_warning'), basename($file), '0B');

            $this->response->redirect($this->url->link('extension/module/zblackbox', 'user_token=' . $this->session->data['user_token'], true));
        }
    }


	protected function validate() {
		if (!$this->user->hasPermission('modify', 'extension/module/zblackbox')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

		return !$this->error;
	}

    public function sendUser() {

        $this->load->language('extension/module/zblackbox');

        //zblackbox Log
        $zblackbox_log = new Log('zblackbox.log');

        if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
            if($this->config->get('module_zblackbox_status') == 1 && $this->config->get('module_zblackbox_apikey') != '') {

                if (empty($_POST['phone_number'])) {
                    $json['error'] = $this->language->get('error_phone_number');
                } elseif (empty($_POST['last_name'])) {
                    $json['error'] = $this->language->get('error_last_name');
                } elseif (empty($_POST['first_name'])) {
                    $json['error'] = $this->language->get('error_first_name');
                } elseif (empty($_POST['np_track'])) {
                    $json['error'] = $this->language->get('error_np_track');
                } elseif (empty($_POST['date'])) {
                    $json['error'] = $this->language->get('error_date');
                } elseif (empty($_POST['cost'])) {
                    $json['error'] = $this->language->get('error_cost');
                } elseif (empty($_POST['comment'])) {
                    $json['error'] = $this->language->get('error_comment');
                } else {

                    $new_phone = preg_replace('/(38|\(|\)|-|\s)/i', '', $_POST['phone_number']);

                    $requestParams = [
                        "id" => 10000,
                        "api_key" => $this->config->get('module_zblackbox_apikey'),   // *Обязательное поле
                        "method" => "add",                                    // *Обязательное поле
                        "type_track" => 1,                                    // *Обязательное поле
                        "phonenumber" => $new_phone,                          // *Обязательное поле
                        "ttn" => $_POST['np_track'],                          // *Обязательное поле
                        "last_name" => $_POST['last_name'],                   // *Обязательное поле
                        "first_name" => $_POST['first_name'],
                        "comment" => $_POST['comment'],
                        "date" => $_POST['date'],
                        "cost" => $_POST['cost']
                    ];

                    $ch = curl_init();

                    curl_setopt($ch, CURLOPT_URL, "http://blackbox.net.ua/api_v2/");
                    curl_setopt($ch, CURLOPT_HEADER, 0);
                    curl_setopt($ch, CURLOPT_POST, 1);
                    curl_setopt($ch, CURLOPT_POSTFIELDS, $requestParams);

                    ob_start();
                    $result = curl_exec($ch);
                    $response = ob_get_contents();
                    ob_end_clean();
                    $err = curl_errno($ch);
                    $errmsg = curl_error($ch);
                    $header = curl_getinfo($ch);

                    curl_close($ch);

                    $zblackbox_result = json_decode($response, true);

                    if (isset($zblackbox_result['success'])) {
                        $json['success'] = $this->language->get('text_introduced_success');

                        $zblackbox_log->write($json['success']);

                    } else {
                        $json['error'] = $zblackbox_result["error"]["message"];

                        $zblackbox_log->write($json['error']);
                    }

                }

            }else{

                $json['success'] = $this->language->get('error_module_off');
            }

        }else{

            $json['success'] = $this->language->get('error_permission');
        }

        $this->response->setOutput(json_encode($json));
    }

    public function userCheck()
    {
        $this->load->language('extension/module/zblackbox');

        //zblackbox Log
        $zblackbox_log = new Log('zblackbox.log');


        if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {

            if($this->config->get('module_zblackbox_status') == 1 && $this->config->get('module_zblackbox_apikey') != ''){


                if (empty($_POST['phone_number2'])) {
                    $json['error'] = $this->language->get('error_phone_number');
                } elseif (empty($_POST['last_name2'])) {
                    $json['error'] = $this->language->get('error_last_name');

                } else {

                    $new_phone = preg_replace('/(38|\(|\)|-|\s)/i', '', $_POST['phone_number2']);

                    $requestParams = [
                        "id" => 10000,
                        "params" => [
                            "phonenumber" => $new_phone,
                            "api_key"     => $this->config->get('module_zblackbox_apikey'),
                        ]
                    ];

                    $ch = curl_init();

                    curl_setopt($ch, CURLOPT_URL, "http://blackbox.net.ua/api/?data=" . json_encode($requestParams));
                    curl_setopt($ch, CURLOPT_HEADER, 0);

                    ob_start();
                    $result  = curl_exec( $ch );
                    $response = ob_get_contents();
                    ob_end_clean();
                    $err     = curl_errno( $ch );
                    $errmsg  = curl_error( $ch );
                    $header  = curl_getinfo( $ch );

                    curl_close( $ch );

                    $zblackbox_result = json_decode($response, true);

                    if(isset($zblackbox_result['success'])){
                        if(isset($zblackbox_result["message"])){

                            $json['success'] = $zblackbox_result["message"];

                            $zblackbox_log->write($json['success']);

                        }elseif (isset($zblackbox_result["data"])){

                            $json['success'] = $this->language->get('text_phone_number_found_success');

                            $zblackbox_log->write($json['success']);


                            foreach($zblackbox_result["data"] as $key => $value) {
                                $json['data'] = $value;
                            }

                            $zblackbox_log->write($json['data']);


                        }
                    }
                    elseif(isset($zblackbox_result['error'])){

                        $zblackbox_log->write($zblackbox_result['error']);
                    }

                }

            }else{

                $json['error'] = $this->language->get('error_module_off');
                $zblackbox_log->write($json['error']);
            }


        }else{
            $json['error'] = $this->language->get('error_permission');
            $zblackbox_log->write($json['error']);
        }

        $this->response->setOutput(json_encode($json));

    }

    public function uninstall() {
        $this->load->model('setting/setting');
        $this->model_setting_setting->deleteSetting('module_zblackbox');

    }
}