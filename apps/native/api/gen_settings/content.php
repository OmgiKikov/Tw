<?php 
# @*************************************************************************@
# @ @author Mansur Altamirov (Mansur_TL)									@
# @ @author_url 1: https://www.instagram.com/mansur_tl                      @
# @ @author_url 2: http://codecanyon.net/user/mansur_tl                     @
# @ @author_email: highexpresstore@gmail.com                                @
# @*************************************************************************@
# @ ColibriSM - The Ultimate Modern Social Media Sharing Platform           @
# @ Copyright (c) 21.03.2020 ColibriSM. All rights reserved.                @
# @*************************************************************************@

if (empty($cl['is_logged'])) {
	$data         = array(
		'code'    => 401,
		'data'    => array(),
		'message' => 'Unauthorized Access'
	);
}
else {
	$user_data_fields = array(
		'fname'       => fetch_or_get($_POST['first_name'], null),
		'lname'       => fetch_or_get($_POST['last_name'], null),
		'about'       => fetch_or_get($_POST['about'], null),
		'email'       => fetch_or_get($_POST['email'], null),
		'gender'      => fetch_or_get($_POST['gender'], null),
		'website'     => fetch_or_get($_POST['website'], null),
		'country_id'  => fetch_or_get($_POST['country_id'], null),
        'uname'       => fetch_or_get($_POST['username'], null)
	);

	foreach ($user_data_fields as $field_name => $field_val) {
		if ($field_name == 'fname') {
			if (empty($field_val) || len_between($field_val, 3, 25) != true) {
	            $data['err_code'] = "invalid_fname";
	            $data['data']     = array();
	            $data['message']  = "";
	            $data['code']     = 400; break;
	        }
		}

		else if ($field_name == 'lname') {
			if (empty($field_val) || len_between($field_val, 3, 25) != true) {
	            $data['err_code'] = "invalid_lname";
	            $data['data']     = array();
	            $data['message']  = "";
	            $data['code']     = 400; break;
	        }
		}

		else if ($field_name == 'about') {
			if (len($field_val, 3, 25) > 140) {
	            $data['err_code'] = "invalid_bio";
	            $data['data']     = array();
	            $data['message']  = "";
	            $data['code']     = 400; break;
	        }
		}

		else if($field_name == 'email') {
			if (empty($field_val) || (filter_var($field_val, FILTER_VALIDATE_EMAIL) != true || len($field_val) > 55)) {
				$data['err_code'] = "invalid_email";
	            $data['data']     = array();
	            $data['message']  = "";
	            $data['code']     = 400; break;
			}

			else if(cl_email_exists($field_val) && ($field_val != $me['email'])) {
				$data['err_code'] = "doubling_email";
	            $data['data']     = array();
	            $data['message']  = "";
	            $data['code']     = 400; break;
			}
		}

		else if ($field_name == 'gender') {
			if (empty($field_val) || in_array($field_val, array('M', 'F')) != true) {
	            $data['err_code'] = "invalid_gender";
	            $data['data']     = array();
	            $data['message']  = "";
	            $data['code']     = 400; break;
	        }
		}

		else if($field_name == 'website') {
        	if (not_empty($field_val) && (is_url($field_val) != true || len($field_val) > 115)) {
        		$data['err_code'] = "website";
	            $data['data']     = array();
	            $data['message']  = "";
	            $data['code']     = 400; break;
        	}
        }

        else if($field_name == 'country_id') {
        	if (not_num($field_val) || (in_array($field_val, array_keys($cl["countries"])) != true)) {
        		$data['err_code'] = "country_id";
	            $data['data']     = array();
	            $data['message']  = "";
	            $data['code']     = 400; break;
        	}
        }

        else if ($field_name == 'uname') {
            if (empty($field_val)) {
                $data['err_code'] = "invalid_uname";
	            $data['data']     = array();
	            $data['message']  = "";
	            $data['code']     = 400; break;
            }

            else if (len_between($field_val,3, 25) != true) {
                $data['err_code'] = "invalid_uname";
	            $data['data']     = array();
	            $data['message']  = "";
	            $data['code']     = 400; break;
            }

            else if (preg_match('/^[\w]+$/', $field_val) != true) {
                $data['err_code'] = "invalid_uname";
	            $data['data']     = array();
	            $data['message']  = "";
	            $data['code']     = 400; break;
            }

            else if(cl_uname_exists($field_val) && $field_val != $me['raw_uname']) {
                $data['err_code'] = "doubling_uname";
	            $data['data']     = array();
	            $data['message']  = "";
	            $data['code']     = 400; break;
            }
        }
	}

	if (empty($data['err_code'])) {
        cl_update_user_data($me["id"], array(
            'fname'      => cl_text_secure($user_data_fields['fname']),
            'lname'      => cl_text_secure($user_data_fields['lname']),
            'username'   => cl_text_secure($user_data_fields['uname']),
            'email'      => cl_text_secure($user_data_fields['email']),
            'about'      => cl_text_secure($user_data_fields['about']),
            'gender'     => cl_text_secure($user_data_fields['gender']),
            'website'    => cl_text_secure($user_data_fields['website']),
            'country_id' => cl_text_secure($user_data_fields['country_id'])
        ));

        if ($user_data_fields['uname'] != $me['raw_uname']) {
            cl_update_user_data($me["id"], array(
                'verified' => '0'
            ));
        }

        $me               = cl_user_data($me["id"]);
        $data["code"]     = 200;
        $data["message"]  = "Profile data updated successfully";
        $data["data"]     = array(
        	'id'          => $me['id'],
        	'first_name'  => $me['fname'],
        	'last_name'   => $me['lname'],
        	'user_name'   => $me['raw_uname'],
        	'email'       => $me['email'],
        	'is_verified' => (($me['verified'] == '1') ? true : false),
        	'website'     => $me['website'],
        	'about_you'   => $me['about'],
        	'gender'      => $me['gender'],
        	'country'     => $me['country_name'],
        	'post_count'  => $me['posts'],
        	'ip_address'  => $me['ip_address'],
        	'following_count' => $me['following'],
        	'follower_count'  => $me['followers'],
        	'language'        => $me['language'],
        	'last_active'     => $me['last_active'],
        	'member_since'    => $me['joined']
        );
    }
}