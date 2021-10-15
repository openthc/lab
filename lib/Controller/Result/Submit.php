<?php
/**
 *
 */

$arg_base = array(

	// 'global_id' => //
	'global_inventory_id' => $Lab_Result->getInventory()['guid'], // ID of Sample at the LAB
	// 'global_for_inventory_id' => $I['guid'], // Says optional but barfs if missing

	// 'global_for_mme_id' => $_SESSION['License']['guid'],

	'testing_status' => 'completed',

	// 'foreign_matter_seeds' => intval($_POST['foreign_matter_seeds']),
	// 'foreign_matter_stems' => intval($_POST['foreign_matter_stems']),
	// 'moisture_content_percent' => _filter_float($_POST['moisture_content_percent']),
	// 'moisture_content_water_activity_rate' => _filter_float($_POST['moisture_content_water_activity_rate']),

	'cannabinoid_status' => 'completed',
	'cannabinoid_d9_thca_percent' => _filter_float($_POST['cannabinoid_d9_thca_percent']),
	'cannabinoid_d9_thca_mg_g' => _filter_float($_POST['cannabinoid_d9_thca_mg_g']),
	'cannabinoid_d9_thc_percent' => _filter_float($_POST['cannabinoid_d9_thc_percent']),
	'cannabinoid_d9_thc_mg_g' => _filter_float($_POST['cannabinoid_d9_thc_mg_g']),
	'cannabinoid_d8_thc_percent' => _filter_float($_POST['cannabinoid_d8_thc_percent']),
	'cannabinoid_d8_thc_mg_g' => _filter_float($_POST['cannabinoid_d8_thc_mg_g']),
	'cannabinoid_thcv_percent' => _filter_float($_POST['cannabinoid_thcv_percent']),
	'cannabinoid_thcv_mg_g' => _filter_float($_POST['cannabinoid_thcv_mg_g']),
	'cannabinoid_cbd_percent' => _filter_float($_POST['cannabinoid_cbd_percent']),
	'cannabinoid_cbd_mg_g' => _filter_float($_POST['cannabinoid_cbd_mg_g']),
	'cannabinoid_cbda_percent' => _filter_float($_POST['cannabinoid_cbda_percent']),
	'cannabinoid_cbda_mg_g' => _filter_float($_POST['cannabinoid_cbda_mg_g']),
	'cannabinoid_cbdv_percent' => _filter_float($_POST['cannabinoid_cbdv_percent']),
	'cannabinoid_cbdv_mg_g' => _filter_float($_POST['cannabinoid_cbdv_mg_g']),
	'cannabinoid_cbg_percent' => _filter_float($_POST['cannabinoid_cbg_percent']),
	'cannabinoid_cbg_mg_g' => _filter_float($_POST['cannabinoid_cbg_mg_g']),
	'cannabinoid_cbga_percent' => _filter_float($_POST['cannabinoid_cbga_percent']),
	'cannabinoid_cbga_mg_g' => _filter_float($_POST['cannabinoid_cbga_mg_g']),
	'cannabinoid_cbc_percent' => _filter_float($_POST['cannabinoid_cbc_percent']),
	'cannabinoid_cbc_mg_g' => _filter_float($_POST['cannabinoid_cbc_mg_g']),
	'cannabinoid_cbn_percent' => _filter_float($_POST['cannabinoid_cbn_percent']),
	'cannabinoid_cbn_mg_g' => _filter_float($_POST['cannabinoid_cbn_mg_g']),

	// Not Used

	'terpenoid_status' => 'complete',
	// 'terpenoid_bisabolol_percent' => _filter_float($_POST['terpenoid_bisabolol_percent']),
	// 'terpenoid_bisabolol_mg_g' => _filter_float($_POST['terpenoid_bisabolol_mg_g']),
	// 'terpenoid_humulene_percent' => _filter_float($_POST['terpenoid_humulene_percent']),
	// 'terpenoid_humulene_mg_g' => _filter_float($_POST['terpenoid_humulene_mg_g']),
	// 'terpenoid_pinene_percent' => _filter_float($_POST['terpenoid_pinene_percent']),
	// 'terpenoid_pinene_mg_g' => _filter_float($_POST['terpenoid_pinene_mg_g']),
	// 'terpenoid_terpinolene_percent' => _filter_float($_POST['terpenoid_terpinolene_percent']),
	// 'terpenoid_terpinolene_mg_g' => _filter_float($_POST['terpenoid_terpinolene_mg_g']),
	// 'terpenoid_b_caryophyllene_percent' => _filter_float($_POST['terpenoid_b_caryophyllene_percent']),
	// 'terpenoid_b_caryophyllene_mg_g' => _filter_float($_POST['terpenoid_b_caryophyllene_mg_g']),
	// 'terpenoid_b_myrcene_percent' => _filter_float($_POST['terpenoid_b_myrcene_percent']),
	// 'terpenoid_b_myrcene_mg_g' => _filter_float($_POST['terpenoid_b_myrcene_mg_g']),
	// 'terpenoid_b_pinene_percent' => _filter_float($_POST['terpenoid_b_pinene_percent']),
	// 'terpenoid_b_pinene_mg_g' => _filter_float($_POST['terpenoid_b_pinene_mg_g']),
	// 'terpenoid_caryophyllene_oxide_percent' => _filter_float($_POST['terpenoid_caryophyllene_oxide_percent']),
	// 'terpenoid_caryophyllene_oxide_mg_g' => _filter_float($_POST['terpenoid_caryophyllene_oxide_mg_g']),
	// 'terpenoid_limonene_percent' => _filter_float($_POST['terpenoid_limonene_percent']),
	// 'terpenoid_limonene_mg_g' => _filter_float($_POST['terpenoid_limonene_mg_g']),
	// 'terpenoid_linalool_percent' => _filter_float($_POST['terpenoid_linalool_percent']),
	// 'terpenoid_linalool_mg_g' => _filter_float($_POST['terpenoid_linalool_mg_g']),

	// @todo Reference the WAC

	'microbial_status' => 'completed',
	// 'microbial_total_viable_plate_count_cfu_g' => 0,
	// 'microbial_total_yeast_mold_cfu_g' => 0,
	// 'microbial_total_coliform_cfu_g' => 0,
	// 'microbial_bile_tolerant_cfu_g' => null,
	// 'microbial_pathogenic_e_coli_cfu_g' => null,
	// 'microbial_salmonella_cfu_g' => null,

	// Required On: Medical Flower, Concentrate (if not already tested)
	// Optional On:

	'mycotoxin_status' => 'completed',
	// 'mycotoxin_aflatoxins_ppb' => null,
	// 'mycotoxin_ochratoxin_ppb' => null,

	// Required On: Medical, Concentrate (if not already tested)
	// Optional On:

	'metal_status' => 'completed',
	// 'metal_arsenic_ppm' => null,
	// 'metal_cadmium_ppm' => null,
	// 'metal_lead_ppm' => null,
	// 'metal_mercury_ppm' => null,

	// @todo May need to be "null" as a string ( "value": "null" )

	// Required on Products: Medical, Concentrate (if not already tested)
	// Optional on All Products: ???

	'pesticide_status' => 'completed',
	// 'pesticide_abamectin_ppm' => _filter_float($_POST['pesticide_abamectin_ppm']),
	// 'pesticide_acequinocyl_ppm' => _filter_float($_POST['pesticide_acequinocyl_ppm']),
	// 'pesticide_bifenazate_ppm' => _filter_float($_POST['pesticide_bifenazate_ppm']),
	// 'pesticide_bifentrin_ppm' => _filter_float($_POST['pesticide_bifentrin_ppm']),
	// 'pesticide_captan_ppm' => _filter_float($_POST['pesticide_captan_ppm']),
	// 'pesticide_cyfluthrin_ppm' => _filter_float($_POST['pesticide_cyfluthrin_ppm']),
	// 'pesticide_cypermethrin_ppm' => _filter_float($_POST['pesticide_cypermethrin_ppm']),
	// 'pesticide_dimethomorph_ppm' => _filter_float($_POST['pesticide_dimethomorph_ppm']),
	// 'pesticide_etoxazole_ppm' => _filter_float($_POST['pesticide_etoxazole_ppm']),
	// 'pesticide_fenhexamid_ppm' => _filter_float($_POST['pesticide_fenhexamid_ppm']),
	// 'pesticide_flonicamid_ppm' => _filter_float($_POST['pesticide_flonicamid_ppm']),
	// 'pesticide_fludioxonil_ppm' => _filter_float($_POST['pesticide_fludioxonil_ppm']),
	// 'pesticide_imidacloprid_ppm' => _filter_float($_POST['pesticide_imidacloprid_ppm']),
	// 'pesticide_myclobutanil_ppm' => _filter_float($_POST['pesticide_myclobutanil_ppm']),
	// 'pesticide_pcnb_ppm' => _filter_float($_POST['pesticide_pcnb_ppm']),
	// 'pesticide_piperonyl_butoxide_ppm' => _filter_float($_POST['pesticide_piperonyl_butoxide_ppm']),
	// 'pesticide_pyrethrin_ppm' => _filter_float($_POST['pesticide_pyrethrin_ppm']),
	// 'pesticide_spinetoram_ppm' => _filter_float($_POST['pesticide_spinetoram_ppm']),
	// 'pesticide_spinosad_ppm' => _filter_float($_POST['pesticide_spinosad_ppm']),
	// 'pesticide_spirotetramet_ppm' => _filter_float($_POST['pesticide_spirotetramet_ppm']),
	// 'pesticide_thiamethoxam_ppm' => _filter_float($_POST['pesticide_thiamethoxam_ppm']),
	// 'pesticide_trifloxystrobin_ppm' => _filter_float($_POST['pesticide_trifloxystrobin_ppm']),

	// Optional:

	'solvent_status' => 'completed',
	// 'solvent_butanes_ppm' => _filter_float($_POST['solvent_butanes_ppm']),
	// 'solvent_heptane_ppm' => _filter_float($_POST['solvent_heptane_ppm']),
	// 'solvent_propane_ppm' => _filter_float($_POST['solvent_propane_ppm']),
	// 'solvent_toluene_ppm' => _filter_float($_POST['solvent_toluene_ppm']),
	// 'solvent_xylene_ppm' => _filter_float($_POST['solvent_xylene_ppm']),
);

__exit_text($arg_base);

$arg = $arg_base;

$Lab_Result = new Lab_Result($_GET['id']);

$metric_list = $Lab_Result->getMetrics();
foreach ($metric_list as $m) {
	$arg[ $m['leafdata_path'] ] = $m['value'];
}

__exit_text($arg_base);

// $rbe = App::rbe();
//$res = $rbe->lab_result()->create($arg);
