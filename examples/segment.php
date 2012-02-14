<?php
/**
 * FlashFoto PHP API SDK - Examples - Add
 * For FlashFoto APIv2
 */

include_once('config.inc.php');
include_once('example.inc.php');
include_once('../flashfoto.php');

$method = 'segment';
$api_url = 'segment';
$doc_url = 'segment';

if(empty($cfg['partner_username']) || empty($cfg['partner_apikey']) || empty($cfg['base_url'])) {
	$error = 'Please configure your settings in config.inc.php';
}

//Group is used if you have 'one of these is required' situations
$required = array(
	'image' => array('type'=>'file', 'group'=>'one'),
	'location' => array('group'=>'one', 'encode'=>'base64'),
);

$optional = array(
	'image_id' => 0,
	'version' => 0,
	'privacy' => array('default'=>'private'),
	'group' => array('default'=>'Image'),
	'format' => array('default'=>'jpeg'),
);

if(!empty($_POST)  && empty($error)) {
	$post_data = validate_form($required, $optional);

	//if no errors proceed
	if(empty($post_data['error'])) {
		$FlashFotoAPI = new FlashFoto($cfg['partner_username'], $cfg['partner_apikey'], $cfg['base_url']);
		try{
			$result = $FlashFotoAPI->add($post_data['api_post_data'] ? $post_data['api_post_data'] : null, $post_data['api_params'] ? $post_data['api_params'] : null);
			$result2 = $FlashFotoAPI->segment($result['Image']['id']);
		} catch(Exception $e) {
			$result = $e;
		}
	} else {
		$error = $post_data['error'];
	}
}

?>

<html>
	<head>
		<title><?php echo ucwords($method); ?> Example - FlashFoto PHP API SDK</title>
		<link href="examples.css" rel="stylesheet" type="text/css" />
	</head>
	<body>
		<noscript class="error">Please enable Javascript!</noscript>

		<h2>
			<a href="<?php echo $cfg['base_url'].'../docs/'.$doc_url; ?>" target="_blank" title="Link to <?php echo ucwords($method); ?> documentation"><?php echo ucwords($method); ?></a>
			Example - FlashFoto PHP API SDK
		</h2>
		
		<div class="error"><?php echo isset($error) ? $error : ''; ?></div>
		<?php if(isset($result)): ?>
		<h2>Add Result:</h2>
		<pre class="success"><?php print_r($result); ?></pre>
		<?php endif; ?>
		<?php if(isset($result2)): ?>
		<h2><?php echo ucwords($method); ?> Result:</h2>
		<pre class="success"><?php print_r($result2); ?></pre>
		<?php endif; ?>

		<h3>URL</h3>
		<?php echo $cfg['base_url'] . $api_url . '/'; ?>

		<form name="form" enctype="multipart/form-data" action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">

			<?php build_output($required, 'Required (choose one):'); ?>

			<?php build_output($optional, 'Optional'); ?>

			<input type="submit" />
		</form>

	</body>
</html>