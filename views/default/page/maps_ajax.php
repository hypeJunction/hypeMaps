<?php

namespace hypeJunction\Maps;

$body = elgg_view('page/elements/body', $vars);

// Set the content type
header("Content-type: text/html; charset=UTF-8");

$lang = get_current_language();
?>
<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="<?php echo $lang; ?>" lang="<?php echo $lang; ?>">
	<body>
		<div class="maps-page-ajax">
			<?php echo $body; ?>
		</div>
	</body>
</html>