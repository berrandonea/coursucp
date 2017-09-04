<?php
/*

This program is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

This plugin is part of Archaius theme, if you use it outside the theme
you should create your own styles. You can use archaius stylesheet as
a example.
@copyright  2014 onwards  Daniel Munera Sanchez

*/
?>
<!-- PAGE HEADER -->

<?php 
//Check if the variable exists, if not you have to create it.
if(! isset($custommenu)){
  $custommenu = $OUTPUT->custom_menu();
}

if(! isset($hascustommenu)){
  $hascustommenu = (empty($PAGE->layout_options['nocustommenu']) && !empty($custommenu));
}
?>
<div id="page-header">
  <div class="page-header-inner">
    <?php if (!empty($PAGE->theme->settings->logo)) { ?>
        <?php $logourl = $PAGE->theme->setting_file_url('logo', 'logo'); ?>
         <div id="logo" class = "nobackground">
              <img class="sitelogo" src="<?php echo $logourl;?>" alt="Custom logo here"
                onclick = "document.location.href = ' <?php echo $CFG->wwwroot ?> '"/>
         </div>
    <?php } else { ?>
      <div id="logo">
          <img class="sitelogo" src="<?php echo $OUTPUT->pix_url('logo','theme')?>" alt="Custom logo here"
            onclick = "document.location.href = ' <?php echo $CFG->wwwroot ?> '" />
      </div>
    <?php } ?>
    <?php if (!empty($PAGE->theme->settings->mobilelogo)) { ?>
        <?php $mobile_logourl = $PAGE->theme->setting_file_url('mobilelogo', 'mobilelogo');?>
        <div id="mobile-logo">
            <img class="sitelogo" src="<?php echo $mobile_logourl;?>" alt="Custom  mobile logo here"
              onclick = "document.location.href = ' <?php echo $CFG->wwwroot ?> '"/>
      </div>
    <?php }else{ ?>
        <div id="mobile-logo">
          <img class="sitelogo" src="<?php echo $OUTPUT->pix_url('mobileLogo','theme')?>" alt="Custom mobile logo here"
            onclick = "document.location.href = ' <?php echo $CFG->wwwroot ?> '" />
        </div>
    <?php } ?>
    <!-- BLOC AJOUTE PAR BRICE -->
    <!-- <div class="page-header-info-container">
    	<div class="wrapper-header-info">
    		<h1>Environnement Numérique</h1>
    		<h1>Pédagogique 2015 - 2016</h1>
    	</div>
    </div> -->
    <div class="page-header-info-container">
      <div class="wrapper-header-info">
        <div class="headermenu">
          <?php if(isloggedin()){ ?>
                <?php global $USER, $COURSE; ?>
                <?php echo $OUTPUT->user_picture($USER, array('courseid'=>$COURSE->id));?>
          <?php }

          //BRICE : si l'utilisateur n'est pas connecté, on n'affiche pas le "custom menu"
          else {
          	$hascustommenu = false;
          }
          ?>

          <?php
          if (isloggedin()) { //BRICE : Retire le "Non connecté" sur la page de connexion
              echo $OUTPUT->login_info();
          } //BRICE
          ?>
          <?php echo $PAGE->headingmenu ?>
        </div>
        <div class='menu-icon deactive'>
          <span class='icon-bar'></span>
          <span class='icon-bar'></span>
          <span class='icon-bar last'></span>
        </div>
      </div>
    </div>
  </div>
  <!-- BRICE -->
  <?php if ($hascustommenu) { ?>
    <div id="custommenu" class="collapsed">
      <div class="custommenu-inner">
      	<?php
      		echo $custommenu;
      	?>
      </div>
      <?php
      if ( ! empty($PAGE->layout_options['nosubtitle'])){
      	$hassubtitle =  !($PAGE->layout_options['nosubtitle']);
      }else{
      	$hassubtitle = true;
      }
      if($hassubtitle){
      ?>
      <table id='ucpsubtitle'>
        <tr height='30px'>
          <td width='20%' style='text-align:center'>
              <!--<button onclick="javascript:hidesubtitle()">Masquer le titre</button>-->
          </td>
          <td width='44%' style='text-align:center'>
              <h1 class = "page-subtitle" style="color:black">
                  <?php echo $PAGE->heading; ?>
              </h1>
          </td>
          <td width='36%' style='text-align:center'>
              <?php echo $PAGE->button; ?>
          </td>
        </tr>
      </table>
      <?php } ?>
    </div>
  <?php
          } ?>
</div>
<!-- END PAGE HEADER -->

<!--BRICE-->
<script>
function hidesubtitle() {
    document.getElementById('ucpsubtitle').style.display = 'none';
}
</script>


