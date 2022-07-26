/**
 * SPDX-License-Identifier: GPL-3.0-only
 */

 $(function() {

	// QOM Handy Picker of All
	$('.lab-metric-qom-bulk').on('click', function() {
		var wrap = $(this).closest('section');
		var sel = this.value;
		if ('OK' == sel) {
			wrap.find('.lab-metric-qom').val('');
		} else {
			wrap.find('.lab-metric-qom').val(sel);
		}
	});

	$('.lab-metric-uom-bulk').on('click', function() {
		var wrap = $(this).closest('section');
		var sel = this.value;
		wrap.find('.lab-metric-uom').val(sel);
	});

	// Attempt Magic on the THC Values?
	var auto_sum = true;
	$('#lab-metric-type-018NY6XC00LMT0HRHFRZGY72C7 input.lab-metric-qom').on('blur change keyup', function() {

		switch (this.id) {
			case 'lab-metric-018NY6XC00V7ACCY94MHYWNWRN':
			case 'lab-metric-018NY6XC00PXG4PH0TXS014VVW':
			case 'lab-metric-018NY6XC00DEEZ41QBXR2E3T97':
			case 'lab-metric-018NY6XC00SAE8Q4JSMF40YSZ3':
				// this.style.borderColor = '#f00';
				// this.setAttribute('data-auto-sum', '0');
				return;
		}

		var sum_all = sum_cbd = sum_thc = 0;
		$('#lab-metric-type-018NY6XC00LMT0HRHFRZGY72C7 input.lab-metric-qom').each(function(i, n) {

			var v = parseFloat(this.value, 10) || 0;
			if (0 == v) {
				return;
			}

			v = (v * 100);

			switch (n.id) {
				case 'lab-metric-018NY6XC00V7ACCY94MHYWNWRN':
				case 'lab-metric-018NY6XC00PXG4PH0TXS014VVW':
				case 'lab-metric-018NY6XC00DEEZ41QBXR2E3T97':
				case 'lab-metric-018NY6XC00SAE8Q4JSMF40YSZ3':
					return; // Ignore These
					break;
				case 'lab-metric-018NY6XC00LM49CV7QP9KM9QH9': // d9-thc
					sum_thc += v;
					break;
				case 'lab-metric-018NY6XC00LMB0JPRM2SF8F9F2': // d9-thca
					sum_thc += (v * 0.877);
					break;
				case 'lab-metric-018NY6XC00LM877GAKMFPK7BMC': // d8-thc ??
					// nothing for now
					break;
				case 'lab-metric-018NY6XC00LMK7KHD3HPW0Y90N': // cbd
					sum_cbd += v;
					break;
				case 'lab-metric-018NY6XC00LMENDHEH2Y32X903': // cbda
					sum_cbd += (v * 0.877);
					break;
			}

			sum_all += v;

		});

		sum_all = sum_all / 100;
		sum_cbd = sum_cbd / 100;
		sum_thc = sum_thc / 100;

		$('#lab-metric-018NY6XC00V7ACCY94MHYWNWRN').val((sum_cbd + sum_thc).toFixed(3));
		$('#lab-metric-018NY6XC00PXG4PH0TXS014VVW').val(sum_thc.toFixed(3));
		$('#lab-metric-018NY6XC00DEEZ41QBXR2E3T97').val(sum_cbd.toFixed(3));
		$('#lab-metric-018NY6XC00SAE8Q4JSMF40YSZ3').val(sum_all.toFixed(3));


	});

	$('.btn-terp-note-auto').on('click', function() {

		var terp_list = [];
		var text_line_list = [];

		$('#lab-metric-type-018NY6XC00LMT07DPNKHQV2GRS input').each(function(i, n) {
			var v = parseFloat(n.value, 10) || 0;
			if (v > 0) {
				terp_list.push({
					node: n,
					value: v,
				});
			}
		});

		// Big on Top
		terp_list.sort(function(a, b) {
			return b.value - a.value;
		});

		var idx = 0;
		var max = Math.min(terp_list.length, 10);

		for (idx=0; idx<max; idx++) {
			var terp = terp_list[idx];
			var node = terp['node'];
			var text = $(node).closest('.input-group').find('.input-group-text').text();
			text_line_list.push(`${text} ${terp.value}`);
		}

		$('#lab-result-terp-note').val(text_line_list.join(', '));

	});

});
