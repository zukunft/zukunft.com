<?php

/*

  test_bootstrap.php - for internal code consistency TESTing
  ------------------
  
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
    along with zukunft.com. If not, see <http://www.gnu.org/licenses/gpl.html>.

    To contact the authors write to:
    Timon Zielonka <timon@zukunft.com>

    Copyright (c) 1995-2021 zukunft.com AG, Zurich
    Heang Lor <heang@zukunft.com>

    http://zukunft.com

*/


$debug = $_GET['debug'] ?? 0;
include_once '../src/main/php/zu_lib.php';

$db_con = prg_start("start test.php");

/*

      $result .= '<ul class="nav nav-tabs" role="tablist">';
      $result .= '  <li class="nav-item active">';
      $result .= '    <a class="nav-link active" role="tab" data-toggle="tab" href="#tab-comp" aria-selected="true">Components</a>';
      $result .= '  </li>';
      $result .= '  <li class="nav-item">';
      $result .= '    <a class="nav-link" role="tab" data-toggle="tab" href="#tab-hist" aria-selected="false">Changes</a>';
      $result .= '  </li>';
      $result .= '  <li class="nav-item">';
      $result .= '    <a class="nav-link" role="tab" data-toggle="tab" href="#tab-link" aria-selected="false">Link changes</a>';
      $result .= '  </li>';
      $result .= '</ul>';
      $result .= '<div class="tab-content border-right border-bottom border-left rounded-bottom">';
      $result .= '  <div id="tab-comp" role="tabpanel" class="tab-pane fade active show">';
      $result .= '    <p>comp</p>';
      //$result .= $this->linked_components($add_cmp, $wrd, $back);
      $result .= '  </div>';
      $result .= '  <div id="tab-hist" role="tabpanel" class="tab-pane fade">';
      $result .= '    <p>hist</p>';
      // display the user changes 
      $result .= '  </div>';
      $result .= '  <div id="tab-link" role="tabpanel" class="tab-pane fade">';
      $result .= '    <p>link</p>';
      $result .= '  </div>';
      $result .= '</div>'; // of tab content
      
      echo $result;
*/

/*

<div class="container">

  <ul class="nav nav-tabs">
    <li class="nav-item active">
    	<a class="nav-link active" data-toggle="tab" href="#tab-comp" aria-selected="true">Components</a>
    </li>
    <li class="nav-item"><a class="nav-link" data-toggle="tab" href="#tab-hist" aria-selected="false">Changes</a></li>
    <li class="nav-item"><a class="nav-link" data-toggle="tab" href="#tab-link" aria-selected="false">Link changes</a></li>
  </ul>

  <div class="tab-content border-right border-bottom border-left rounded-bottom">
    <div id="tab-comp" class="tab-pane fade in active show">
      <h3>comp</h3>
      <p>Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.</p>
    </div>
    <div id="tab-hist" class="tab-pane fade">
      <h3>hist</h3>
      <p>Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat.</p>
    </div>
    <div id="tab-link" class="tab-pane fade">
      <h3>link</h3>
      <p>Sed ut perspiciatis unde omnis iste natus error sit voluptatem accusantium doloremque laudantium, totam rem aperiam.</p>
    </div>
  </div>
</div>

*/
?>

<script>
    $('#tokenfield').tokenfield({
        autocomplete: {
            source: ['red', 'blue', 'green', 'yellow', 'violet', 'brown', 'purple', 'black', 'white'],
            delay: 100
        },
        showAutocompleteOnFocus: true
    })
</script>

<input type="text" class="form-control" id="tokenfield" value="red,green,blue"/>


<?php

/*


<ul class="nav nav-tabs" id="myTab" role="tablist">
  <li class="nav-item">
    <a class="nav-link active" id="homes-tab" data-toggle="tab" href="#homes" role="tab" aria-controls="homes" aria-selected="true">Home</a>
  </li>
  <li class="nav-item">
    <a class="nav-link" id="profiles-tab" data-toggle="tab" href="#profiles" role="tab" aria-controls="profiles" aria-selected="false">Profile</a>
  </li>
  <li class="nav-item">
    <a class="nav-link" id="contacts-tab" data-toggle="tab" href="#contacts" role="tab" aria-controls="contacts" aria-selected="false">Contact</a>
  </li>
</ul>
<div class="tab-content" id="myTabContent">
  <div class="tab-pane fade show actives" id="homes" role="tabpanel" aria-labelledby="homes-tab">home</div>
  <div class="tab-pane fade" id="profiles" role="tabpanel" aria-labelledby="profiles-tab">profile</div>
  <div class="tab-pane fade" id="contacts" role="tabpanel" aria-labelledby="contacts-tab">contact</div>
</div>

<div id="phrases">
  <input class="typeahead" data-provide="typeahead" type="text" placeholder="word or formula">
</div>
	

<br>
<br>


  <ul class="nav nav-tabs" id="myTab" role="tablist">
    <li class="nav-item">
      <a class="nav-link active show" id="home-tab" data-toggle="tab" href="#home" role="tab" aria-controls="home" aria-selected="true">Home</a>
    </li>
    <li class="nav-item">
      <a class="nav-link" id="profile-tab" data-toggle="tab" href="#profile" role="tab" aria-controls="profile" aria-selected="false">Profile</a>
    </li>
    <li class="nav-item">
      <a class="nav-link" id="contact-tab" data-toggle="tab" href="#contact" role="tab" aria-controls="contact" aria-selected="false">Contact</a>
    </li>
  </ul>
  <div class="tab-content" id="myTabContent">
    <div class="tab-pane fade active show" id="home" role="tabpanel" aria-labelledby="home-tab">
      <p>Raw denim you probably haven't heard of them jean shorts Austin. Nesciunt tofu stumptown aliqua, retro synth master cleanse. Mustache cliche tempor, williamsburg carles vegan helvetica. Reprehenderit butcher retro keffiyeh dreamcatcher synth. Cosby sweater eu banh mi, qui irure terry richardson ex squid. Aliquip placeat salvia cillum iphone. Seitan aliquip quis cardigan american apparel, butcher voluptate nisi qui.</p>
    </div>
    <div class="tab-pane fade" id="profile" role="tabpanel" aria-labelledby="profile-tab">
      <p>Food truck fixie locavore, accusamus mcsweeney's marfa nulla single-origin coffee squid. Exercitation +1 labore velit, blog sartorial PBR leggings next level wes anderson artisan four loko farm-to-table craft beer twee. Qui photo booth letterpress, commodo enim craft beer mlkshk aliquip jean shorts ullamco ad vinyl cillum PBR. Homo nostrud organic, assumenda labore aesthetic magna delectus mollit. Keytar helvetica VHS salvia yr, vero magna velit sapiente labore stumptown. Vegan fanny pack odio cillum wes anderson 8-bit, sustainable jean shorts beard ut DIY ethical culpa terry richardson biodiesel. Art party scenester stumptown, tumblr butcher vero sint qui sapiente accusamus tattooed echo park.</p>
    </div>
    <div class="tab-pane fade" id="contact" role="tabpanel" aria-labelledby="contact-tab">
      <p>Etsy mixtape wayfarers, ethical wes anderson tofu before they sold out mcsweeney's organic lomo retro fanny pack lo-fi farm-to-table readymade. Messenger bag gentrify pitchfork tattooed craft beer, iphone skateboard locavore carles etsy salvia banksy hoodie helvetica. DIY synth PBR banksy irony. Leggings gentrify squid 8-bit cred pitchfork. Williamsburg banh mi whatever gluten-free, carles pitchfork biodiesel fixie etsy retro mlkshk vice blog. Scenester cred you probably haven't heard of them, vinyl craft beer blog stumptown. Pitchfork sustainable tofu synth chambray yr.</p>
    </div>
  </div>

<br>
<br>


                
<input id="tokenfield" type="text" class="form-control" value="red,green,blue" />

<br>
<br>

<h2>Filterable Table</h2>
<p>Type something in the input field to search the table for first names, last names or emails:</p>  
<input id="myInput" type="text" placeholder="Search..">
<br><br>

<table>
  <thead>
    <tr>
      <th>Firstname</th>
      <th>Lastname</th>
      <th>Email</th>
    </tr>
  </thead>
  <tbody id="myTable">
    <tr>
      <td>John</td>
      <td>Doe</td>
      <td>john@example.com</td>
    </tr>
    <tr>
      <td>Mary</td>
      <td>Moe</td>
      <td>mary@mail.com</td>
    </tr>
    <tr>
      <td>July</td>
      <td>Dooley</td>
      <td>july@greatstuff.com</td>
    </tr>
    <tr>
      <td>Anja</td>
      <td>Ravendale</td>
      <td>a_r@test.com</td>
    </tr>
  </tbody>
</table>


  $result = ''; // reset the html code var
  $msg    = ''; // to collect all messages that should be shown to the user immediately

  // load the session user parameters
  $usr = New user;
  $result .= $usr->get();

  // check if the user is permitted (e.g. to exclude google from doing stupid stuff)
  if ($usr->id > 0) {
    $dsp = new view_dsp;
    $dsp->id = cl(SQL_VIEW_LINK_EDIT);
    $dsp->usr = $usr;
    $dsp->load();
    $back = $_GET['back']; // the original calling page that should be shown after the change if finished

    $result .= $dsp->dsp_navbar($back);

    $result .= '  <br><br>';
    $result .= '  <form class="form-inline my-2 my-lg-0" action="/http/find.php">';
    $result .= '    <input class="form-control mr-sm-2" type="search" id="pattern" placeholder="word or formula" aria-label="Search">';
    $result .= '    <button class="btn btn-outline-success my-2 my-sm-0" type="submit">Get numbers</button>';
    $result .= '  </form>';
    
    echo $result;
  }  

?>

Test bootstrap
<?php 
*/

// Free result set
mysqli_free_result();

// Closing connection
prg_end($db_con);

?>
