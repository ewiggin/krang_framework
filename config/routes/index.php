<?php
/**
 * Routes
 * Definim les rutes de l'aplicació.
 * Totes les rutes funcionen amb una variable $_GET['u'] que defineix
 * en la secció que estem.
 *
 * Si la secció te una acció associada, la definim en aquest multiarray.
 *
 */

$Router->get('/', 'HomeController::hello');
?>