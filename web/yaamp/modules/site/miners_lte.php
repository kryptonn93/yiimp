<?php

$algo = user()->getState('yaamp-algo');

JavascriptFile("/extensions/jqplot/jquery.jqplot.js");
JavascriptFile("/extensions/jqplot/plugins/jqplot.dateAxisRenderer.js");
JavascriptFile("/extensions/jqplot/plugins/jqplot.barRenderer.js");
JavascriptFile("/extensions/jqplot/plugins/jqplot.highlighter.js");
JavascriptFile('/yaamp/ui/js/auto_refresh.js');

$height = '240px';

openMainContent();
?>

<!-- Auto Refresh -->
<div id='resume_update_button'style='color: #ffffff; background-color: #41464b; border: 1px solid #7d7d7d;
  padding: 10px; margin-left: 20px; margin-right: 20px; margin-top: 15px; cursor: pointer; display: none;'
  onclick='auto_page_resume();' align=center>
    <div class="alert alert-warning alert-dismissible">
      <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
      <h5><i class="icon fas fa-exclamation-triangle"></i> Auto Refresh Is Paused - Click Here To Resume </h5>
    </div>
  </div>
  <!-- ./Auto Refresh -->

<div class="row">
  <div class="col-lg-6">
    <div id='miners_results'>
      <br><br><br><br><br><br><br><br><br><br>
      <br><br><br><br><br><br><br><br><br><br>
    </div>
   </div> <!-- col-lg-6 close -->

   <div class="col-lg-6">
    <div id='pool_current_results'>
     <br><br><br><br><br><br><br><br><br><br>
    </div>
    
  </div> <!-- col-lg-6 close -->
</div> <!-- row close -->

<?php closeMainContent(); ?>
<script>

function page_refresh()
{
    miners_refresh();
    pool_current_refresh();
}

function select_algo(algo)
{
    window.location.href = '/site/algo?algo='+algo+'&r=/site/miners';
}

////////////////////////////////////////////////////

function pool_current_ready(data)
{
    $('#pool_current_results').html(data);
}

function pool_current_refresh()
{
    var url = "/site/current_results";
    $.get(url, '', pool_current_ready);
}

////////////////////////////////////////////////////

function miners_ready(data)
{
    $('#miners_results').html(data);
}

function miners_refresh()
{
    var url = "/site/miners_results";
    $.get(url, '', miners_ready);
}

</script>


