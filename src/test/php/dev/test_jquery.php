<?php

/*

  test_jquery.php - for internal code consistency TESTing
  ---------------
  
    This file is part of zukunft.com - calc with words

    zukunft.com is free software: you can redistribute it and/or modify it
    under the terms of the GNU General Public License as
    published by the Free Software Foundation, either version 3 of
    the License, or (at your option) any later version.
    zukunft.com is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with zukunft.com. If not, see <http://www.gnu.org/licenses/agpl.html>.

    To contact the authors write to:
    Timon Zielonka <timon@zukunft.com>

    Copyright (c) 1995-2022 zukunft.com AG, Zurich
    Heang Lor <heang@zukunft.com>

    http://zukunft.com

*/


$debug = $_GET['debug'] ?? 0;
const ROOT_PATH = __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR;
include_once ROOT_PATH . 'src/main/php/zu_lib.php';

$db_con = prg_start("start test.php");

/*

<!DOCTYPE html>
<html>
<head>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>

<script>
$(document).ready(function(){
    $("button").click(function(){
      var xmlhttp = new XMLHttpRequest();
      xmlhttp.onreadystatechange = function() {
          if (this.readyState == 4 && this.status == 200) {
              var myObj = JSON.parse(this.responseText);
              $("div").append(myObj.name + " ");
          }
      };
      xmlhttp.open("GET", "/http/get_json.php", true);
      xmlhttp.send(); 
    });
});
</script>
</head>
<body>

<button>Get JSON data</button>

<div></div>

</body>
</html>

<!DOCTYPE html>
<html>
<body>

<h2>Get data as JSON from a PHP file, and convert it into a JavaScript array.</h2>

<p id="demo"></p>

<script>

var xmlhttp = new XMLHttpRequest();

xmlhttp.onreadystatechange = function() {
    if (this.readyState == 4 && this.status == 200) {
        var myObj = JSON.parse(this.responseText);
        document.getElementById("demo").innerHTML = myObj[2];
    }
};
xmlhttp.open("GET", "https://zukunft.com/http/get_json.php?term=C", true);
// xmlhttp.open("GET", "https://zukunft.com/http/get_json_test.php", true);
xmlhttp.timeout = 4000; // Set timeout to 4 seconds (4000 milliseconds)
xmlhttp.ontimeout = function () { alert("Timed out!!!"); }
xmlhttp.send();

</script>

</body>
</html>

// Free resultset
mysqli_free_result($result);

// Closing connection
zu_end($db_con);

      source: words
      source: "https://zukunft.com/http/get_json.php"
      source: "https://zukunft.com/http/get_json.php",
      select: function( event, ui ) {
          event.preventDefault();
          $("#tags").val(ui.item.id);
      }
      source: function( request, response ) {
        var term = request.term;
        if ( term in cache ) {
          response( cache[ term ] );
          return;
        }
        $.getJSON( "//zukunft.com/http/get_json.php?query=" + request.term, function( data ) {
          response( data );
        });
      }
      source: function( request, response ) {
        $.getJSON( "https://zukunft.com/http/get_json.php?term=" + request.term, function( data ) {
          response( data );
        });
      }
      source: function( request, response ) {
        $.getJSON( "/http/get_json.php?query=" + request.term, function( data ) {
          response( data );
        });
        var term = request.term;
        if ( term in cache ) {
          response( cache[ term ] );
          return;
        }

      source: function (query, result) {
        $.ajax({
          url: "https://zukunft.com/http/get_json.php",
          data: 'query=' + query,            
          dataType: "json",
          type: "POST",
          success: function (data) {
            result($.map(data, function (item) {
              return item;
            }));
          }
        });
      }  

      $.getJSON("/http/get_json.php", function(result){
         $.each(result, function(i, field){
              $("div").append(field + " ");
          });
      });
 
*/
?>

<script>
    $(document).ready(function () {
        $("#tags").autocomplete({
            source: function (query, result) {
                $.ajax({
                    url: "https://zukunft.com/http/get_json.php",
                    data: 'term=' + query,
                    dataType: "json",
                    type: "POST",
                    success: function (data) {
                        result($.map(data, function (item) {
                            return item;
                        }));
                    }
                });
            }
        });
    });
    // console.log(source);
</script>

<form method="post" action="/form" autocomplete="off">
    <div class="ui-widget">
        <label for="tags">Tags: </label>
        <input id="tags">
    </div>
</form>


<?php
/*
    $( "#tags" ).autocomplete({
      function ( request, response ) {
        $.getJSON( "https://zukunft.com/http/get_json.php?term=" + request.term, function( data ) {
          response( data );
        });
      }
    });
    
    $( "#tags" ).autocomplete({
      source: availableTags
    });
      source: function (query, result) {
        $.ajax({
          url: "/http/get_json.php",
          data: 'term=' + query,            
          dataType: "json",
          type: "POST",
          success: function (data) {
            result($.map(data, function (item) {
              return item;
            }));
          }
        });
      }
      
      source: function( request, response ) {
        var term = request.term;
        if ( term in cache ) {
          response( cache[ term ] );
          return;
        }
        $.getJSON( "/http/get_json.php?term=" + request.term, function( data ) {
          response( data );
        });
      }
      
*/

?>
