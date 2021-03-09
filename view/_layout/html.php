<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<meta name="apple-mobile-web-app-capable" content="yes">
<meta name="mobile-web-app-capable" content="yes">
<meta name="viewport" content="initial-scale=1, user-scalable=yes">
<meta name="application-name" content="OpenTHC Lab">
<meta name="apple-mobile-web-app-title" content="OpenTHC Lab">
<meta name="theme-color" content="#247420">
<meta name="description" content="OpenTHC Lab Data Portal">

<!-- OG -->
<meta property="og:image" content="https://cdn.openthc.com/img/icon.png">
<meta property="og:title" content="<?= strip_tags($data['Page']['title']) ?: 'OpenTHC Lab' ?>">
<meta property="og:description" content="OpenTHC Lab Data Portal">

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.13.0/css/all.min.css" integrity="sha256-h20CPZ0QyXlBuAw7A+KluUYx/3pK+c7lYEpqLTlxjYQ=" crossorigin="anonymous">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.css" integrity="sha256-rByPlHULObEjJ6XQxW/flG2r+22R5dKiAoef+aXWfik=" crossorigin="anonymous">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.4.0/css/bootstrap.min.css" integrity="sha256-/ykJw/wDxMa0AQhHDYfuMEwVb4JHMx9h4jD4XvHqVzU=" crossorigin="anonymous">
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
</style>
<title><?= strip_tags($data['Page']['title']) ?: 'OpenTHC Lab' ?></title>
</head>
<body>

<?= $this->block('_block/menu-zero.php') ?>
<?= $this->block('_block/flash-messages.php') ?>

<?= $this->body ?>

<?= $this->block('_block/footer-zero.php') ?>

<!-- Library Deps -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.4.1/jquery.min.js" integrity="sha256-CSXorXvZcTkaix6Yvo6HppcZGetbYMGWSFlBw8HfCJo=" crossorigin="anonymous"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.js" integrity="sha256-KM512VNnjElC30ehFwehXjx1YCHPiQkOPmqnrWtpccM=" crossorigin="anonymous"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.4.0/js/bootstrap.min.js" integrity="sha256-oKpAiD7qu3bXrWRVxnXLV1h7FlNV+p5YJBIr8LOCFYw=" crossorigin="anonymous"></script>

<?= $this->foot_script ?>

</body>
</html>
