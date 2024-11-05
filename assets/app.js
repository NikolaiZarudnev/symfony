/*
 * Welcome to your app's main JavaScript file!
 *
 * We recommend including the built version of this JavaScript file
 * (and its CSS file) in your base layout (base.html.twig).
 */
// import Routing from 'fos-router';


// any CSS you import will output into a single css file (app.css in this case)
import './styles/app.css';

import './styles/home.css';

import './scripts/user';

import './scripts/account';

import './scripts/product';

import './scripts/order';


// import '../public/bundles/datatables/js/cdn.datatables.net_1.13.6_js_jquery.dataTables.min'
// import '../public/bundles/datatables/js/cdn.datatables.net_v_dt_jq-3.7.0_dt-1.13.6_datatables.min'
// import '../public/bundles/datatables/js/cdn.datatables.net_1.13.6_js_dataTables.bootstrap5.min'

require('bootstrap');

// or you can include specific pieces
// require('bootstrap/js/dist/tooltip');
// require('bootstrap/js/dist/popover');

$(document).ready(function() {
    $('[data-toggle="popover"]').popover();
});