<?php
	return [
		'report_to'						=> env('REPORT_TO','charush@accubits.com'),
		'col_count' 					=> env('COL_COUNT',3), // number of columns
		'col_name' 						=> [
			'module_code','module_name','module_term' // possible column names
		],
		'rules'							=> [
			'/^[a-zA-Z0-9 ]+$/', 		// will indicate the first column validation rules
			'/^[a-zA-Z0-9 ]+$/', // will indicate the second column validation rule
			'/^[a-zA-Z0-9 ]+$/', // will indicate the third column validation rule
		]
	]; 
?>