<?php 


//function used to validate forms from example
function validate_form($required = array(), $optional = array()) {
	$api_post_data = null;
	$api_params = array();
	$error = null;

	if(!empty($required) && is_array($required)) {
		$empty_count = array();
		$group_count = array();
		foreach($required as $req => $params) {
			if(empty($_POST[$req])) {
				//could be a file
				if(isset($params['type']) && $params['type'] == 'file' && !empty($_FILES[$req]['size'])) {
					$api_post_data = file_get_contents($_FILES[$req]['tmp_name']);
					if(isset($params['group'])) {
						$group_count[$params['group']][] = 1;
					}
					//nope, it's empty, check if it's part of a group
				} elseif(isset($params['group'])) {
					$group_count[$params['group']][] = 1;
					$empty_count[$params['group']][] = 1;
					//nope, throw an error
				} else {
					$error = 'A required field is missing';
				}
			} else {
				if(isset($params['encode'])) {
					switch($params['encode']) {
						case 'base64':
							$api_params[$req] = base64_encode($_POST[$req]);
							break;
						default:
							$api_params[$req] = $_POST[$req];
					}
				} else {
					$api_params[$req] = $_POST[$req];
				}

				if(isset($params['group'])) {
					$group_count[$params['group']][] = 1;
				}
			}
		}
		//see if all objects of a group are missing
		if(!empty($empty_count)) {
			foreach($empty_count as $group_name => $groups) {
				if(count($groups) == count($group_count[$group_name])) {
					$error = 'A required field is missing';
				}
			}
		}
	}

	if(!empty($optional) && is_array($optional)) {
		foreach($optional as $opt => $params) {
			if(isset($params['type']) && $params['type'] == 'file' && !empty($_FILES[$opt]['size'])) {
				$api_post_data = file_get_contents($_FILES[$opt]['tmp_name']);
			}
			if(!empty($_POST[$opt])) {
				if(isset($params['encode'])) {
					switch($params['encode']) {
						case 'base64':
							$api_params[$opt] = base64_encode($_POST[$opt]);
							break;
						default:
							$api_params[$opt] = $_POST[$opt];
					}
				} else {
					$api_params[$opt] = $_POST[$opt];
				}
			}
		}
	}

	return array('api_post_data' => $api_post_data, 'api_params' => $api_params, 'error'=>$error);
}

//used to output proper html elements
function build_output($var_array, $category) {
	if(!empty($var_array) && is_array($var_array)) {
		echo '<h3>'.$category.'</h3>';
		foreach($var_array as $var => $params) {
			echo '<label for="'.$var.'">'.$var . (!isset($params['default']) ? '' : ' <em>(default: '.$params['default'].')</em>'). ': ';
			echo '<input type="'. (!isset($params['type']) ? 'text' : $params['type']) . '" id="'.$var.'" name="'.$var.'"/>';
			echo '</label>';
		}
	}
}