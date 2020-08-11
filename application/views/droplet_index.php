<?php
defined('BASEPATH') OR exit('No direct script access allowed');
?><!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<title>Welcome to CodeIgniter</title>

<style type="text/css">

::selection { background-color: #E13300; color: white; }
::-moz-selection { background-color: #E13300; color: white; }

body {
	background-color: #fff;
	margin: 40px;
	font: 13px/20px normal Helvetica, Arial, sans-serif;
	color: #4F5155;
}

a {
	color: #003399;
	background-color: transparent;
	font-weight: normal;
}

#container {
	margin: 10px;
	border: 1px solid #D0D0D0;
	box-shadow: 0 0 8px #D0D0D0;
}
</style>

<style>
table {
  font-family: arial, sans-serif;
  border-collapse: collapse;
  width: 100%;
}

td, th {
  border: 1px solid #dddddd;
  text-align: left;
  padding: 8px;
}

tr:nth-child(even) {
  background-color: #dddddd;
}
</style>

</head>
<body>

<div id="container">
<h1>Welcome to LMS!</h1>
<span><?php echo anchor('droplet/createDroplet', 'Create Droplet', 'title="Create new droplet"'); ?></span>
<div id="body">
<table class="table table-light">
    <thead class="thead-light">
        <tr>
            <th>Id</th>
            <th>Ip Address</th>
            <th>Name</th>
            <th>Action</th>
        </tr>
    </thead>
    <tbody>
    <?php
    if (isset($dropletData)){
        foreach ($dropletData->droplets as $data) { ?>
            <tr class="">
                <td><?php echo $data->id ?> </td>
                <td><?php echo $data->networks->v4[0]->ip_address ?> </td>
                <td><?php echo $data->name?> </td>
                <td>
                    <?php echo anchor('droplet/destroy/'.$data->id.'/'.$data->networks->v4[0]->ip_address, 'Delete Droplet', 'title="Delete droplet"'); ?>
                    </form>
                </td>
            </tr>
            <?php
        }
    }
?>
    </tbody>
</table>
</div>

</body>
</html>