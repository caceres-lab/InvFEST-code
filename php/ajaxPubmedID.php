<?php
/******************************************************************************
	AJAXPUBMEDID.PHP

	Allows the searching for an specific study in the database by its PubMed ID.
	When you retrieve the complete information for that study, you will be able to add the study for the current inversion.
	It is executed by the php/new_study.php script
*******************************************************************************/

    // Retrieve the query and generate the URL.
    $pmid = $_GET["q"];
    $url = "http://eutils.ncbi.nlm.nih.gov/entrez/eutils/efetch.fcgi?db=pubmed&id=";
    $url = $url . $pmid . "&retmode=xml";

    // Create a new DOM object for the article.
    $domArticle = new DOMDocument();

    // Load article information from NCBI.
    $domArticle->load($url);
    $article = $domArticle->getElementsByTagName("Article")->item(0);

    /*
    // Display result in a table.
    echo '<table style="width:800px;">';

    // PMID
    echo "<tr><th>PMID</th><td>" . $pmid . "</td></tr>";

    // Journal
    $text = $article->getElementsByTagName("ISOAbbreviation")->item(0)->nodeValue;
    echo "<tr><th>Journal</th><td>" . $text . "</td></tr>";

    // Year
    $text = $article->getElementsByTagName("Year")->item(0)->nodeValue;
    echo "<tr><th>Year</th><td>" . $text . "</td></tr>";

    // Article Title
    $text = $article->getElementsByTagName("ArticleTitle")->item(0)->nodeValue;
    echo "<tr><th>Article Title</th><td>" . $text . "</td></tr>";

    echo "</table>";
    */

    $author = $article->getElementsByTagName("LastName")->item(0)->nodeValue;
    $authorInit = $article->getElementsByTagName("Initials")->item(0)->nodeValue;

    $otherAuthor = $article->getElementsByTagName("LastName")->item(2)->nodeValue;
    if($otherAuthor == "") {
	    $author2 = $article->getElementsByTagName("LastName")->item(1)->nodeValue;
	    $author2 .= " ";
	    $author2 .= $article->getElementsByTagName("Initials")->item(1)->nodeValue;
	    if ($author2 != "") { $author2 = " and $author2"; }
    }
    else { $author2 = "et al."; }

    $year = $article->getElementsByTagName("Year")->item(0)->nodeValue;
    $journal = $article->getElementsByTagName("ISOAbbreviation")->item(0)->nodeValue;

    // Solucionar caràcters estranys al nom de l'estudi
    setlocale(LC_CTYPE, 'en_US.utf8');
    $study = iconv('UTF-8', 'ASCII//TRANSLIT',"$author $author2 $year");

    echo "
        <input type='text' name='study' id='study' value='$study'/>   
        <input type='hidden' name='author' id='author' value='$author $authorInit $author2' />
        <input type='hidden' name='year' id='year' value='$year' />
        <input type='hidden' name='journal' id='journal' value='$journal' /> 
        ";

    // <input type='text' name='study' id='study' value='$author $authorInit$author2 $year $journal'/>   


?>

