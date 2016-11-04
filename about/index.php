<?php include("../load.php");?>
<html>
 <head>
  <?php head("About");?>
 </head>
 <body>
  <?php headerElem();?>
  <div class="container" style="width:300px;">
   <h2>About</h2>
   <p>Web Search is a simple software written in PHP that functions as a search engine. It's called <b>WS</b>.</p>
   <p>WS's crawler is named Dingo. It will crawl about 100 pages each minute and indexes them.</p>
   <p>WS don't store any cookies and is perfectly safe.</p>
   <h2>Key features</h2>
   <ul>
    <li>No Ads</li>
    <li>New Results By The Minute</li>
    <li>No Cookies</li>
    <li>No Malcious Tracking</li>
   </ul>
   <p>Site Stats are tracked using StatCounter. You can see it <a target="_blank" href="http://statcounter.com/p9729182/summary/?guest=1">here</a>.</p>
   <p>Donate if you like my work</p><br/>
   <center><form action="https://www.paypal.com/cgi-bin/webscr" method="post" target="_top"><input type="hidden" name="cmd" value="_s-xclick"><input type="hidden" name="hosted_button_id" value="ZYQWUZ2B8ZXXA"><button name="submit" type="submit"><img alt="Donate" src="https://www.paypalobjects.com/en_US/i/btn/btn_donateCC_LG.gif"></button><img alt="Donate" src="https://www.paypalobjects.com/en_GB/i/scr/pixel.gif" width="1" height="1" border="0"></form></center>
  </div>
  <?php footer();?>
 </body>
</html>
