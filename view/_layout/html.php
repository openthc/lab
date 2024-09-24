<?php
/**
 * Main Theme
 *
 * SPDX-License-Identifier: GPL-3.0-only
 */

// $doc = new HTML();
// $doc->addHead($head);
// $doc->addBody($body);
// $doc->addScript();

?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="initial-scale=1, user-scalable=yes">
<meta name="application-name" content="OpenTHC Lab">
<meta name="theme-color" content="#069420">

<!-- OG -->
<meta property="og:image" content="https://openthc.org/img/icon.png">
<meta property="og:title" content="<?= strip_tags($data['Page']['title']) ?: 'OpenTHC Lab' ?>">
<meta property="og:description" content="OpenTHC Lab Data Portal">

<link rel="stylesheet" href="/vendor/fontawesome/css/all.min.css">
<link rel="stylesheet" href="/vendor/bootstrap/bootstrap.min.css">
<link rel="stylesheet" href="/vendor/jquery/jquery-ui.min.css">
<link rel="stylesheet" href="https://cdn.openthc.com/css/www/0.0.3/www.css">
<style>
.form-control::placeholder {
	font-style: italic;
}

.form-group label {
	font-weight: bold;
}

.c {
	text-align: center;
}
.r {
	text-align: right;
}

.menu-zero {
	align-items: stretch;
	background: #111;
	display: flex;
	flex-wrap: wrap;
	font-size: 1.5rem;
	font-weight: bold;
	/* height: 2.8125rem; */
	justify-content: space-between;
	position: sticky;
	top: 0;
	z-index: 1;
}
.menu-zero a.nav-link  {
	color: #fff;
}
.menu-zero a.nav-link:hover {
	color: #247420;
}

.menu-zero-home {
	flex: 0 0 auto;
}
.menu-zero-item {
	display: flex;
	flex: 1 1 auto;
	flex-direction: row;
	justify-content: flex-start;
}

.page-full {
	margin: 0;
	padding: 1rem;
}

.page-thin {
	margin: 0 auto;
	max-width: 640px;
	padding: 1rem;
}

/**
 * Product Grid Details
 */
.product-grid {
	display: flex;
	flex-wrap: wrap;
	margin: 0 -0.50%;
	max-width: 1920px;
}
.product-grid .product-item {
	/* display: flex; */
	flex-basis: 100%;
	flex-grow: 1;
	flex-shrink: 1;
	padding: 0 0.50% 0.50% 0.50%;
	max-width: 100%;
}
.product-grid .product-item .card-img-top {
	max-height: 15vh;
	object-fit: cover;
	object-position: center;
}

#img-zoom {
	align-items: center;
	background: #fcfcfc99;
	border: 4px solid #333;
	bottom: 5vh;
	display: flex;
	/* flex-direction: row; */
	justify-content: center;
	left: 5vw;
	position: fixed;
	text-align: center;
	top: 5vh;
	right: 5vw;
}
#img-zoom img {
	flex: 0 1 auto;
	height: auto;
	margin: 0 auto;
	max-width: 100%;
}

@media (min-width: 600px) {
	.product-grid .product-item {
		flex-basis: 50%;
		max-width: 50%;
	}
}
@media (min-width: 900px) {
	.product-grid .product-item {
		flex-basis: 25%;
		max-width: 25%;
	}
}
@media (min-width: 1200px) {
	.product-grid .product-item {
		flex-basis: 20%;
		max-width: 20%;
	}
}

/**
 * Result Shit
 */
.lab-metric-grid {
	display: flex;
	flex-direction: row;
	flex-wrap: wrap;
	/* justify-content: space-between; */
	margin: 0 -0.25rem;
}
.lab-metric-item {
	flex: 1 0 33.3333%;
	/* max-width: 30rem; */
	min-width: 15rem;
	padding: 0.25rem;
}
.lab-metric-item .input-group .input-group-text {
	display: block;
	overflow: hidden;
	text-align: left;
	text-overflow: ellipsis;
	white-space: nowrap;
	width: 10em;
}
.lab-metric-item .input-group .input-group-text:hover {
	min-width: 10em;
	width: auto;
}
.lab-metric-item::after {
	content: '';
	flex: auto;
}

.result-metric-wrap {
	display: flex;
	flex-direction: row;
	flex-wrap: wrap;
	margin: 0;
	padding: 0;
}

.result-metric-data {
	flex: 1 0 33%;
	padding: 0 0.125rem 0.50rem 0.125rem;
	min-width: 15em;
}



/**
 * Config Shit
 */
.config-wrap {
	align-items: center;
	display: flex;
	flex-direction: row;
	flex-wrap: wrap;
	justify-content: space-evenly;
}

.config-wrap .config-card {
	flex: 1 1 50%;
	min-width: 32em;
	padding: 2vh 2vw;
}

</style>
<title><?= strip_tags($data['Page']['title']) ?: 'OpenTHC Lab' ?></title>
</head>
<body>

<?= $this->block('menu-zero.php') ?>

<!-- <?= $this->block('flash-messages.php') ?> -->

<?php
$x = \Edoceo\Radix\Session::flash();
if ( ! empty($x)) {

	// Upscale Radix Style to Bootstrap
	$x = str_replace('<div class="good">', '<div class="alert alert-success alert-dismissible" role="alert">', $x);
	$x = str_replace('<div class="info">', '<div class="alert alert-info alert-dismissible" role="alert">', $x);
	$x = str_replace('<div class="warn">', '<div class="alert alert-warning alert-dismissible" role="alert">', $x);
	$x = str_replace('<div class="fail">', '<div class="alert alert-danger alert-dismissible" role="alert">', $x);
	$x = str_replace('</div>', '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>', $x);

	if ( ! empty($x)) {
		echo sprintf('<div class="container-fluid my-4">%s</div>', $x);
	}

}
?>

<!-- @todo Remove container-fluid -->
<main class="container-fluid" style="min-height:80vh;">
<?= $this->body ?>
</main>

<?= $this->block('footer-zero.php') ?>

<!-- Library Deps -->
<script src="/vendor/jquery/jquery.min.js"></script>
<script src="/vendor/jquery/jquery-ui.min.js"></script>
<script src="/vendor/bootstrap/bootstrap.bundle.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/clipboard.js/2.0.4/clipboard.min.js" integrity="sha256-FiZwavyI2V6+EXO1U+xzLG3IKldpiTFf3153ea9zikQ=" crossorigin="anonymous"></script>
<script>
$(function() {

	$('#qa-metric-wrap .collapse').removeClass('show');

	$('.custom-file-input').on('change',function(e) {
		//var f = $(this).val();
		var f = e.target.files[0].name;
		$(this).next('.custom-file-label').addClass("selected").html(f);
	});

	$('.btn-transfer-sync').on('click', function() {
		var $b = $(this);
		$b.find('i').addClass('fa-spin');
		var arg = {
			a: 'sync',
			id: $b.data('id')
		};
		$.post('/transfer/sync', arg, function() {
			$b.find('i').removeClass('fa-spin');
		});
	});

	var clipcopy = new ClipboardJS('.btn-clipcopy');
	clipcopy.on('error', function(e) {
		alert(e);
	});
	clipcopy.on('success', function(e) {

		e.clearSelection();

		var $btn = $(e.trigger);
		$btn.tooltip({
			title: 'Copied!',
			trigger: 'manual',
		});
		$btn.tooltip('show');
		setTimeout(function() {
			$btn.tooltip('dispose');
		}, 2000);

	});

	// License Autocomplete Everywhere
	$('.license-autocomplete').autocomplete({
		source: 'https://directory.openthc.com/api/autocomplete/license',
		search: function(e, ui) {
			$(this.parentElement).data('pick', false);
			$(this.parentElement).find('input[type="hidden"]').val('');
			$(this.parentElement).find('.btn-autocomplete-hint i').addClass('fa-spin');
			$(this.parentElement).find('.btn-autocomplete-hint').addClass('btn-outline-secondary').removeClass('btn-outline-success btn-outline-warning');
		},
		response: function(e, ui) {
			if (0 == ui.content.length) {
				$(this).autocomplete('close');
			}
		},
		close: function (e,ui) {
			$(this.parentElement).find('.btn-autocomplete-hint i').removeClass('fa-spin');
		},
		select: function (e,ui) {
			$(this.parentElement).data('pick', true);
			$(this.parentElement).find('.autocomplete-data-id').val(ui.item.id);
			$(this.parentElement).find('.btn-autocomplete-hint').addClass('btn-outline-success').removeClass('btn-outline-secondary')
			$('#license-id-link').attr('href', `https://directory.openthc.com/license/${ui.item.id}`);
			$('#license-id-link').addClass('btn-primary').removeClass('btn-outline-secondary');
		}
	});

	// Product
	$('.product-autocomplete').autocomplete({
		source: 'https://pdb.openthc.org/api/autocomplete',
		search: function(e, ui) {
			var $pe = $(this.parentElement);
			$pe.data('pick', false);
			$pe.find('input[type="hidden"]').val('');
			$pe.find('.btn-autocomplete-hint i').addClass('fa-spin');
			$pe.find('.btn-autocomplete-hint').addClass('btn-outline-secondary').removeClass('btn-outline-success btn-outline-warning');
		},
		response: function(e, ui) {
			if (0 == ui.content.length) {
				$(this).autocomplete('close');
			}
		},
		select: function(e, ui) {
			debugger;
			var $pe = $(this.parentElement);
			$pe.data('pick', true);
			$pe.find('input[type="hidden"]').val(ui.item.id);
			$pe.find('.btn-autocomplete-hint').addClass('btn-outline-success').removeClass('btn-outline-secondary')
		},
		close: function(e, ui) {
			var $pe = $(this.parentElement);
			$pe.find('.btn-autocomplete-hint i').removeClass('fa-spin');
			var pick = $pe.data('pick');
			if (!pick) {
				$pe.find('.btn-autocomplete-hint').addClass('btn-outline-warning').removeClass('btn-outline-secondary');
			}
		}
	});

	// Variety
	$('.variety-autocomplete').autocomplete({
		source: 'https://vdb.openthc.org/api/autocomplete',
		search: function(e, ui) {
			$(this.parentElement).data('pick', false);
			$(this.parentElement).find('input[type="hidden"]').val('');
			$(this.parentElement).find('.btn-autocomplete-hint i').addClass('fa-spin');
			$(this.parentElement).find('.btn-autocomplete-hint').addClass('btn-outline-secondary').removeClass('btn-outline-success btn-outline-warning');
		},
		response: function(e, ui) {
			if (0 == ui.content.length) {
				$(this).autocomplete('close');
			}
		},
		select: function(e, ui) {
			$(this.parentElement).data('pick', true);
			$(this.parentElement).find('input[type="hidden"]').val(ui.item.variety.id);
			$(this.parentElement).find('.btn-autocomplete-hint').addClass('btn-outline-success').removeClass('btn-outline-secondary')
		},
		close: function(e, ui) {
			var $pe = $(this.parentElement);
			$pe.find('.btn-autocomplete-hint i').removeClass('fa-spin');
			var pick = $pe.data('pick');
			if (!pick) {
				$pe.find('.btn-autocomplete-hint').addClass('btn-outline-warning').removeClass('btn-outline-secondary');
			}
		}
	});

});
</script>
<?= $this->foot_script ?>
</body>
</html>
