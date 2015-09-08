<?php error_log(__FILE__);?>
<?php include 'archive.page.php'; ?>
<?php unset($site_data, $err, $result);?>
<div class="block">
  <?php
    $err = '';
    $site_data = $site->status($auth->acc_id);
    if (!$site_data[0]) {
        $err = $site_data[1];
        goto end;
    }

    if (!$site_name) {
        $err = 'site does not exist';
        goto end;
    }

    if (!array_key_exists($site_name, $site_data[1])) {
        $err = 'You do not have permission to backup this site';
    } else {
        $result = $site->archive($auth->acc_id, $site_name);
    }
    end:
  ?>
  <h3>Backup Results</h3>
    <?php
      if (strlen($err) > 0) {
          echo '<p class="error">'.$err."</p>\n";
      } else {
          if (!$result[0]) {
              echo '<p class="error">'.$result[1]."</p>\n";
          } else {
              echo '<p>Backup of site "'.$site_name."\" started successfully.</p>\n";
          }
      }
    ?>
</div>
<?php unset($site_data, $err, $result);?>