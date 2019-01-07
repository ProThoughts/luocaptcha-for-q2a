<?php

class qa_luocaptcha
{
	private $directory;

	public function load_module($directory, $urltoroot)
	{
		$this->directory = $directory;
	}

	public function admin_form()
	{
		$saved = false;

		if (qa_clicked('luocaptcha_save_button')) {
			qa_opt('luocaptcha_site_key', qa_post_text('luocaptcha_site_key_field'));
			qa_opt('luocaptcha_api_key', qa_post_text('luocaptcha_api_key_field'));

			$saved = true;
		}

		$siteKey = trim(qa_opt('luocaptcha_site_key'));
		$apiKey = trim(qa_opt('luocaptcha_api_key'));

		$error = null;
		if (!strlen($siteKey) || !strlen($apiKey)) {
			$error = 'To use luocaptcha, you must <a href="//luosimao.com/service/captcha" target="_blank">sign up</a> to get these keys.';
		}

		$form = array(
			'ok' => $saved ? 'luocaptcha settings saved' : null,

			'fields' => array(
				'public' => array(
					'label' => 'luocaptcha site key:',
					'value' => $siteKey,
					'tags' => 'name="luocaptcha_site_key_field"',
				),

				'private' => array(
					'label' => 'luocaptcha api Key:',
					'value' => $apiKey,
					'tags' => 'name="luocaptcha_api_key_field"',
					'error' => $error,
				),
			),

			'buttons' => array(
				array(
					'label' => 'Save Changes',
					'tags' => 'name="luocaptcha_save_button"',
				),
			),
		);

		return $form;
	}

	public function allow_captcha()
	{
		$siteKey = trim(qa_opt('luocaptcha_site_key'));
		$apiKey = trim(qa_opt('luocaptcha_api_key'));

		return strlen($siteKey) && strlen($apiKey);
	}

	public function form_html(&$qa_content, $error)
	{
		$siteKey = qa_opt('luocaptcha_site_key');
		$qa_content['script_src'][] = '//captcha.luosimao.com/static/dist/api.js';
		$qa_content['script_lines'][] = array(
			'function getResponse(resp){',
			'	//LUOCAPTCHA.reset()',
			'}'
		);

		$html = '<div class="l-captcha" data-site-key="'.$siteKey.'" data-callback="getResponse"></div>';

		return $html;
	}

	public function validate_post(&$error)
	{
		$url = 'https://captcha.luosimao.com/api/site_verify';
		$response = qa_post_text('luotest_response');
		$apiKey = qa_opt('luocaptcha_api_key');

		$params = array('api_key'=>$apiKey,'response'=>$response);
		$postdata = http_build_query($params);
		$opts = array('http' =>
			array(
				'method'  => 'POST',
				'header'  => 'Content-type: application/x-www-form-urlencoded',
				'content' => $postdata
			)
		);

		$context = stream_context_create($opts);
		$result = file_get_contents($url, false, $context);
		$result = json_decode($result,true);

		if($result['error']==0){
			return true;
		}else{
			$error = $result['msg'];
			return false;
		}
	}
}
