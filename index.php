<?php require "load.php";?>
<html>
  <head>
    <?php head("", array("index"));?>
  </head>
  <body>
    <?php headerElem();?>
    <div class="container">
      <center>
        <h1>Web Search</h1>
        <form class="searchForm" action="search.php" method="GET">
          <input type="text" autocomplete="off" name="q" id="query"/>
          <div>
            <button>
              <svg class='shape-search' viewBox="0 0 100 100" class='shape-search'><use xlink:href='#shape-search'></use></svg>
            </button>
          </div>
          <p>Free, Open Source & Anonymous</p>
          <p><a href="https://github.com/subins2000/search">GitHub (Source Code)</a></p>
          <p><a href="about">About</a></p>
        </form>
      </center>
    </div>
    <?php footer();?>
  </body>
</html>
