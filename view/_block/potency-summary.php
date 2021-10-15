<?php
/**
 * Potency Result Cards
 */

?>

<div class="row card-wrap potency-result-cards">
	<div class="col">
		<div class="card">
			<div class="card-body">
				<h2 class="c" style="margin:0;">THC: <span class="potency-result-thc"><?= $data['Result']['thc'] ?></span></h2>
			</div>
		</div>
	</div>
	<div class="col">
		<div class="card">
			<div class="card-body">
				<h2 class="c" style="margin:0;">CBD: <span class="potency-result-cbd"><?= $data['Result']['cbd'] ?></span></h2>
			</div>
		</div>
	</div>
	<div class="col">
		<div class="card">
			<div class="card-body">
				<h2 class="c" style="margin:0;">Total: <span class="potency-result-total"><?= $data['Result']['sum'] ?></span></h2>
			</div>
		</div>
	</div>
</div>
