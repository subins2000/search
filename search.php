<?php require_once "load.php";?>
<html>
  <head>
    <?php head($GLOBALS['displayQ'], array("search"));?>
  </head>
  <body>
    <?php headerElem();?>
    <div class="container">
      <script>document.getElementById('query').focus();</script>
      <?php 
      if($GLOBALS['q'] == ""){
        echo "A query please...";
      }else{
      require "inc/class.spellcheck.php";
      $corSp = \Fr\SC::check($GLOBALS['q']);
      if($corSp != null){
       echo "<p style='color:red;font-size:15px;margin-bottom:10px'>Did you mean ?  <br/><a href='?q=$corSp'>".$corSp."</a></p>";
      }
      $res = getResults();
      if($res === 0){
        echo "<p>Sorry, no results were found</p><h3>Search Suggestions</h3>";
        echo "<ul>";
        echo "<li>Check your spelling</li>";
        echo "<li>Try more general words</li>";
        echo "<li>Try different words that mean the same thing</li>";
        echo "</ul>";
      }else{
      ?>
      <div class="info">
        <strong><?php echo $res['count'];?></strong>
        <?php echo $res['count']==1 ? "result" : "results";?> found in  <?php echo $res['time'];?> seconds. Page  <?php echo $GLOBALS['p'];?>
      </div>
      <div class="results">
        <?php 
        foreach($res['results'] as $re){
          $t=htmlFilt($re[0]);
          $u=htmlFilt($re[1]);
          $d=htmlFilt($re[2]);
          if(strlen($GLOBALS['q']) > 2){
            $d=str_replace($GLOBALS['q'], "<strong>{$GLOBALS['q']}</strong>", $d);
          }
        ?>
          <div class="result">
            <h3 class="title">
              <a target="_blank" onmousedown="this.href='<?php echo HOST;?>/url.php?u='+encodeURIComponent(this.getAttribute('data-href'));" data-href="<?php echo $u;?>" href="<?php echo $u;?>"><?php echo strlen($t)>59 ? substr($t, 0, 59)."..":$t;?></a>
            </h3>
            <p class="url" title="<?php echo $u;?>"><?php echo $u;?></p>
            <p class="description"><?php echo $d;?></p>
          </div>
        <?php 
        }
        ?>
      </div>
      <div class="pages">
        <?php 
        $count=(ceil($res['count']/10));
        $start=1;
        if($GLOBALS['p'] > 5 && $count > ($GLOBALS['p'] + 4)){
          $start=$GLOBALS['p']-4;
          $count=$count > ($start+8) ? ($start+8):$count;
        }elseif($GLOBALS['p'] > 5){
          if($GLOBALS['p']==$count){
            $start=$GLOBALS['p']-8;
          }elseif($GLOBALS['p']==($count-1)){
            $start=$GLOBALS['p']-7;
          }elseif($GLOBALS['p']==($count-2)){
            $start=$GLOBALS['p']-6;
          }elseif($GLOBALS['p']==($count-3)){
            $start=$GLOBALS['p']-5;
          }elseif($GLOBALS['p']==($count-4)){
            $start=$GLOBALS['p']-4;
          }
        }elseif($GLOBALS['p']  <= 5 && $count > ($GLOBALS['p'] + 5)){
          $count=$start+8;
        }
        for($i=$start;$i<=$count;$i++){
          $isC=$GLOBALS['p']==$i ? 'current':'';
          echo "<a href='?p=$i&q={$GLOBALS['q']}' class='button $isC'>$i</a>";
        }
        ?>
      </div>
        <?php    
        }
      }
      ?>
    </div>
    <?php footer();?>
  </body>
</html>
