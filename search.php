<?php
/******************************************************************************
  SEARCH.PHP

  Inversions query box page ("Search" menu from the website).
  Query is sent to search_invdb2.php
*******************************************************************************/
?>


<?php
  // Session start for the PHP
  session_start();
?>

<!DOCTYPE html>
<html>

<?php
  
  // Select specific data into variables which are retrieved in other php pages
  include_once('php/select_index.php');

   // Select specific data that is used to the "Add inversion validation" menu
  include_once('php/select_report.php');

  // Includes HTML <head> and other settings for the page
  include_once('php/structure_page.php');

?>

<?php 
    echo $creator;

    $head .= $head_search;  // Attach the 'search form' scripts within the HTML header
    $head .= "</head>";     // Head end
    echo $head;             // 'Print' head code
?>

<!-- **************************************************************************** -->
<!-- BODY -->
<!-- **************************************************************************** -->
<body>

    <!-- **************************************************************************** -->
    <!-- PAGE MENU: Print the header banner of InvFEST -->
    <!-- **************************************************************************** --> 
    <?php include('php/echo_menu.php'); ?>

    <!-- Show the forms for searching and adding inversions -->
    <?php
        echo '<br />';
        echo $search_inv; // Search form
        echo '<br />';
        if ($_SESSION["autentificado"]=='SI') {
            echo $add_pred; // Add prediction form
            echo '<br />';
        }
    ?>

    <!-- **************************************************************************** -->
    <!-- DIVISIONS -->
    <!-- **************************************************************************** -->
    <div id="welcome" class="section-content">
        <p class="classP_justified">
            <b>Sample queries</b>
        </p>
        <p class="classP_justified">
            You can retrieve InvFEST inversions by using the query box above. A genome position can be specified by a chromosomal coordinate range, a cytological band, the InvFEST accession number of an inversion, or a gene symbol. The following list shows examples of valid position queries: 
        </p>
        <ul>
            <li>
                <b><a href="search_invdb2.php?search_field=chr17">chr17</a></b>
                &nbsp;&nbsp;&nbsp;>&nbsp;&nbsp;&nbsp;<b>all of chromosome 17</b>
            </li>
            <li>
                <b><a href="search_invdb2.php?search_field=chr17:-42140000">chr17:-42140000</a></b>
                &nbsp;&nbsp;&nbsp;>&nbsp;&nbsp;&nbsp;<b>chromosome 17 from the start of the chromosome to position 42140000</b>
            </li>
            <li>
                <b><a href="search_invdb2.php?search_field=chr17:40920000-">chr17:40920000-</a></b>
                &nbsp;&nbsp;&nbsp;>&nbsp;&nbsp;&nbsp;<b>chromosome 17 from position 40920000 to the end of the chromosome</b>
            </li>
            <li>
                <b><a href="search_invdb2.php?search_field=chr17:40920000-42140000">chr17:40920000-42140000</a></b>
                &nbsp;&nbsp;&nbsp;>&nbsp;&nbsp;&nbsp;<b>chromosome 17 from position 40920000 to position 42140000</b>
            </li>
            <li>
                <b><a href="search_invdb2.php?search_field=17q">17q</a></b>
                &nbsp;&nbsp;&nbsp;>&nbsp;&nbsp;&nbsp;<b>region for band q on chromosome 17</b>
            </li>
            <li>
                <b><a href="search_invdb2.php?search_field=17q21">17q21</a></b>
                &nbsp;&nbsp;&nbsp;>&nbsp;&nbsp;&nbsp;<b>region for band q21 on chromosome 17</b>
            </li>
            <li>
                <b><a href="search_invdb2.php?search_field=17q21.31">17q21.31</a></b>
                &nbsp;&nbsp;&nbsp;>&nbsp;&nbsp;&nbsp;<b>region for band q21.31 on chromosome 17</b>
            </li>
            <li>
                <b><a href="search_invdb2.php?search_field=HsInv0573">HsInv0573</a></b>
                &nbsp;&nbsp;&nbsp;>&nbsp;&nbsp;&nbsp;<b>inversion with accession HsInv0573</b>
            </li>
            <li>
                <b><a href="search_invdb2.php?search_field=MAPT">MAPT</a></b>
                &nbsp;&nbsp;&nbsp;>&nbsp;&nbsp;&nbsp;<b>inversions affecting gene MAPT</b>
            </li>
        </ul>
        <p class="classP_justified">
            Additionally, you can refine your search by adding one or more filters (click at <b>< Add filter ></b> and select appropriate options). <br/>&nbsp;<br/>
        </p>

        <div id="assembly">
            <p class="classP_justified"><b>About the reference assembly</b></p>
            <p class="classP_justified">
                Coordinates for all inversions in the InvFEST database are according to the March 2006 human reference sequence (<b>NCBI Build 36.1, hg18</b>) produced by the International Human Genome Sequencing Consortium. 
                The NCBI Build 36.1 reference sequence is considered to be "finished", a technical term indicating that the sequence is highly accurate (with fewer than one error per 10,000 bases) and highly contiguous (with the only remaining gaps corresponding to regions whose sequence cannot be reliably resolved with current technology). 
                For further information on this assembly, see: 
            </p>
            <ul>
                <li><a href="http://www.ncbi.nlm.nih.gov/genome/assembly/2928/" target=\"_blank\">
                    NCBI Assembly database for Build 36.1
                </a></li>
                <li><a href="http://www.ncbi.nlm.nih.gov/genome/guide/human/release_notes.html#b36" target=\"_blank\">
                    NCBI Build 36.1 release notes
                </a></li>
                <li><a href="http://www.ncbi.nlm.nih.gov/mapview/stats/BuildStats.cgi?taxid=9606&build=36&ver=1" target=\"_blank\">
                    NCBI Build 36.1 Statistics
                </a></li>
            </ul>
            <p class="classP_justified">
                However, our database can be queried using hg19 coordinates as well. These hg19 coordinates will be lifted over to hg18 coordinates before performing the query to InvFEST, and the resulting hg18 coordinates will be used to query the database. <br/>&nbsp;<br/>
                Further information can be found in the Help section <a href="help.php#faq6">“Why do we not translate inversion coordinates into hg19?”</a>.
            </p>
        </div>
    </div>

    <br />

    <!-- **************************************************************************** -->
    <!-- FOOT OF THE PAGE -->
    <!-- **************************************************************************** -->
    <div id="foot">
        <?php include('php/footer.php'); ?>
    </div>

</div> <!-- Closes the Wrapper's divison opened at 'echo_menu.php' -->
</body>
</html>
