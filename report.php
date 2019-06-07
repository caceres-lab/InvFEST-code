<?php
/******************************************************************************
	REPORT.PHP

	Report page for the specified inversion. It is divided in 9 sections (General information, Region map, Predictions, Validations and genotyping, Frequency, Breakpoints, Evolutionary history, Functional effects, and Report history).
	The inversion's information is mainly retrieved from the database through select_report.php
*******************************************************************************/
?>


<!-- Session start for the PHP -->
<?php session_start(); ?>

<!DOCTYPE html>
<html>

<!--
	2009 Miquel Ràmia <miquel.ramia@uab.cat>
	Lab. de Bioinformàtica de la variació genètica
	Grup de Genòmica, Bioinformàtica i Evolució
	Departament de Genètica i Microbiolgia
	Universitat Autònoma de Barcelona   

	2012 Raquel Egea <raquel.egea@uab.cat>
	Servei de Genomica i Bioinformatica
	Institut de Biologia i Biotecnologia
	Universitat Autonoma de Barcelona
-->

<?php
    
	// 
    include_once('php/select_report.php');
    
    // Includes global variables
    include_once('php/php_global_variables.php');
    
    // Connection to the database
    include('php/db_conexion.php');

?>

<?php /*
    if ($_SESSION["autentificado"]=='SI' && ($r['status']!='Withdrawn' && $r['status']!='withdrawn') {
      include_once('php/select_new_validation.php');
    }

    */
    $_SESSION['current_report'] = "http://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
?>
    

<head>

    <!-- **************************************************************************** -->
    <!-- SITE SETTINGS -->
    <!-- **************************************************************************** -->
    <!-- Font to use within the entire site -->
	<link rel='stylesheet' type='text/css'
          href='http://fonts.googleapis.com/css?family=Ubuntu'>

    <!-- Favicon (32x32) to show on the explorer's address bar --> 
	<link rel="shortcut icon" href="img/InvFEST_ico.png">

    <!-- Page title -->
	<title>
        Inversion Report: <?php echo $r['name']; ?>
    </title>

    <!-- Site metadata -->
	<meta charset='UTF-8'>


    <!-- **************************************************************************** -->
    <!-- STYLES -->
    <!-- **************************************************************************** -->
	<link rel="stylesheet" type="text/css" href="css/style.css" />
	<link rel="stylesheet" type="text/css" href="css/report.css" />
    <link rel='stylesheet' type='text/css' href='css/search.css'/>
	<!-- For the highslide: -->
	<link rel="stylesheet" type="text/css" href="css/css_highslide.css" />
    <!-- 2016/04 Graphs in map: styles of the frequency section -->
    <link rel="stylesheet" type="text/css" href="css/freq_section.css" />

    <!-- **************************************************************************** -->
    <!-- SCRIPTS -->
    <!-- **************************************************************************** -->
	<!-- Include JQuery from a CDN (or locally if the site is offline) -->
	<script src="https://code.jquery.com/jquery-1.4.2.min.js"></script>
	<script>
        $(window).jQuery || $(document).write('<script src="js/jquery-1.4.2.min.js" async><\/script>')
    </script>

    <!-- **************************************************************************** -->
	<!-- Other scripts -->
	<script src="js/validations.js"></script>
  	<script src="js/header.js"></script>

    <!-- **************************************************************************** -->
    <!-- 2016/04 Graphs in map modification -->
    <script>

        // DETECTOR: check if canvas is suported by the explorer ****************************
        function isCanvasSupported(id) {
            var elem = document.createElement('canvas');
            if (!(elem.getContext && elem.getContext('2d'))) { alert("Sorry, your explorer does not support HTML5 Canvas"); }
            else {
                msieversion(); //Check explorer
                clearMap(id);
                loadCanvas(id);
            }
        }

        // MSIEVersion: detects if user is using MS Internet Explorer
        function msieversion() {
            var ua = window.navigator.userAgent;
            var msie = ua.indexOf("MSIE ");

            var alerted = localStorage.getItem('alerted') || '';
            if ((alerted != 'yes') && (msie > 0 || !!navigator.userAgent.match(/Trident.*rv\:11\./))) {
                // If Internet Explorer, return version number
                alert('Internet Explorer may not visualize all the data properly');
                localStorage.setItem('alerted', 'yes');
            }
        }

        // LOAD CANVAS **********************************************************************
        function loadCanvas(id) {

            //*******************************************************************************
            // PIES CLASS *******************************************************************
            var pie = (function () {

                // Constructor
                function pie(id, x, y, value, total, radius, cutPosition, hovercolor, blurcolor) {
                    this.id = id;
                    this.posX = x;
                    this.posY = y;
                    this.value = value || 0;
                    this.total = total || 0;
                    this.radius = radius;
                    this.cutPosition = cutPosition || 0;
                    this.startAngle = 2 * Math.PI * cutPosition || 0;
                    this.endAngle = 2 * Math.PI * (cutPosition + (value / total)) || 0;
                    this.hovercolor = hovercolor;
                    this.blurcolor = blurcolor;
                    this.isHovering = false;
                    this.redraw(this.posX, this.posY);
                    return (this);
                }

                //
                pie.prototype.redraw = function (x, y) {
                    this.posX = x || this.posX;
                    this.posY = y || this.posY;
                    this.draw(false);
                    return (this);
                }

                //
                pie.prototype.highlight = function (x, y) {
                    this.posX = x || this.posX;
                    this.posY = y || this.posY;
                    this.draw(true);
                    return (this);
                }

                //
                pie.prototype.draw = function (isHovering) {
                    //
                    ctx.save();
                    // Draw piechart
                    ctx.beginPath();
                    if (isHovering) { this.radius = global_Radius + 1; }
                    else { this.radius = global_Radius - 1; }

                    if (this.value == this.total && this.total > 0) {                       // No STD || no INV
                        ctx.arc(this.posX, this.posY, this.radius, this.startAngle, this.endAngle, true);

                    } else if (this.value <= 0) {
                        // Don't draw the pie if there isn't a value to represent
                    } else if (this.total <= 0) {                                           // No STD, no INV >> Should never happen
                        ctx.arc(this.posX, this.posY, this.radius, 0, 2 * Math.PI, true);   // Draw a gray pie
                        this.blurcolor = blurColors[2];
                        this.hovercolor = blurColors[2];
                    } else {                                                                // There are both STD and INV alleles
                        ctx.moveTo(this.posX, this.posY);
                        ctx.arc(this.posX, this.posY, this.radius, this.startAngle, this.endAngle, false);
                        ctx.lineTo(this.posX, this.posY);
                    }

                    ctx.closePath();
                    ctx.fillStyle = isHovering ? this.hovercolor : this.blurcolor;
                    ctx.fill();
                    // Draw piechart's border
                    ctx.strokeStyle = "#FFFFFF";
                    ctx.lineWidth = 1;
                    ctx.stroke();
                    //
                    ctx.restore();
                }

                //
                pie.prototype.isPointInside = function (x, y, total) {
                    // Next lines define a new 'arc' which es never drawn.
                    // It helps to determine if the mouse (x,y) is over the pie with the method isPointInPath()

                    ctx.beginPath();
                    if (this.value > 0) {   // Only check if mouse is inside when there's a value represented
                        if (total > 0) {
                            ctx.arc(this.posX, this.posY, this.radius, this.startAngle, this.endAngle);
                        } else {
                            ctx.arc(this.posX, this.posY, this.radius, 0, 2 * Math.PI);
                        }
                        ctx.arc(this.posX, this.posY, 0, 0, 0, false);
                    }

                    if (ctx.isPointInPath(x, y)) { return true; }
                    else { return false; }
                }

                return pie;

            })();

            //***********************************************************************************
            // RECTANGLE GENERATOR: create a canvas rectangle with rounded corners **********
            function roundRect(ctx, x, y, width, height, radius, fill, stroke) {
                if (typeof stroke == "undefined") {
                    stroke = true;
                }
                if (typeof radius === "undefined") {
                    radius = 5;
                }
                ctx.beginPath();
                ctx.moveTo(x + radius, y);
                ctx.lineTo(x + width - radius, y);
                ctx.quadraticCurveTo(x + width, y, x + width, y + radius);
                ctx.lineTo(x + width, y + height - radius);
                ctx.quadraticCurveTo(x + width, y + height, x + width - radius, y + height);
                ctx.lineTo(x + radius, y + height);
                ctx.quadraticCurveTo(x, y + height, x, y + height - radius);
                ctx.lineTo(x, y + radius);
                ctx.quadraticCurveTo(x, y, x + radius, y);
                ctx.closePath();
                if (stroke) {
                    ctx.stroke();
                }
                if (fill) {
                    ctx.fill();
                }
            }

            // TOOLTIP GENERATOR: draw a tooltip on mouse position with the ID data of a pie 
            function drawTooltip(x, y, id) {

                // Elements to show within the tooltip
                id = id.trim();
                var ttelements = id.split(";");                                 // Population; Type (STD, INV, OTHER); Frequency[%]
                var str = ttelements[0] + ': ' + ttelements[1] + ' = ' + ttelements[2];

                // Settings for the tooltip
                var fontSize = 8;
                var rectWidth = ctx.measureText(str).width * 0.9;               // Similar to the text width
                var rectHeight = fontSize * 2;                                  // Double the text size
                var rectX = x * 1.01;                                           // A little bit more to the right than the mouse position X
                if ((rectX + rectWidth) > canvas.width) {                       // If canvas limits are exceeded, show tooltip to the left
                    rectX = (x - rectWidth) * 1.01;
                }
                var rectY = y - rectHeight * 1.08;                              // Above the mouse position Y
                if ((rectY - rectHeight) < 0) {                                 // If canvas limits are exceeded, show tooltip under the mouse
                    rectY = (y + rectHeight * 1.08);
                }
                var cornerRadius = fontSize / 1.6;                              // Related to the text size

                // Rectangle
                ctx.strokeStyle = "#191970";
                ctx.fillStyle = "rgba(255, 255, 255, .75)";
                roundRect(ctx, rectX, rectY, rectWidth, rectHeight, cornerRadius, true);

                // Text
                ctx.fillStyle = "rgba(0, 0, 0, .9)";
                ctx.font = 'bold ' + fontSize + 'pt Arial';
                ctx.textAlign = 'center';
                ctx.fillText(str, rectX + rectWidth / 2, rectY + rectHeight / 1.375);

            }

            // LEGEND GENERATOR *************************************************************
            function drawLegend() {
                // Legend labels
                var text = ["STD", "INV"];

                // Legend settings
                var posX = 10, posY = 10;
                var sizeLegend = Math.min(canvas.width, canvas.height) / 60;

                // Text settings
                ctx.font = "11pt Arial";
                ctx.textAlign = 'left';

                // Legend elements
                for (var i = 0; i < text.length; i++) {
                    ctx.fillStyle = blurColors[i];
                    ctx.fillRect(posX, posY, sizeLegend, sizeLegend);
                    posX += sizeLegend * 1.5;
                    ctx.fillText(text[i], posX, posY * 2.2);
                    posX += ctx.measureText(text[i]).width + sizeLegend * 2;
                }
            }

            // MOUSE MOVEMENT HANDLER for pies (arcs) ***************************************
            function handleMouseMove(e) {
                //
                var area = canvas.getBoundingClientRect();
                mouseX = Math.floor((e.clientX - area.left) / (area.right - area.left) * canvas.width);
                mouseY = Math.floor((e.clientY - area.top) / (area.bottom - area.top) * canvas.height);

                // Clear canvas
                ctx.clearRect(0, 0, canvas.width, canvas.height);

                // Draw world map and legend again
                // a) X/Y position
                // b) X/Y original size (if we want to crop Antarctica for example, we chose a smaller Y than the original)
                // c) X/Y position inside the canvas
                // d) X/Y final size
                ctx.drawImage(map, 0, 0, xMapSize, yMapSize, 0, 0, canvas.width, canvas.height);
                drawLegend();

                // References that will help to draw a tooltip a the end (on top) with the pie info
                var isPieHL = false;
                var refPieId = "";

                // Redraw elements
                for (var i = 0; i < pies.length; i++) {
                    if (pies[i].isPointInside(mouseX, mouseY, pies[i].total)) {
                        pies[i].highlight();
                        refPieId = pies[i].id;
                        isPieHL = true;
                    } else {
                        pies[i].redraw();
                    }
                }

                // Draw the tooltip with the info
                if (isPieHL) { drawTooltip(mouseX, mouseY, refPieId); }
                else { isPieHL = false; }
            }

            // GET DATA *************************************************************************
            function getData() {

                var data = [];

                // Take all the checkbox-type objects
                var chkbTable = document.getElementsByClassName("regChkbox");   // Array with the regions

                for (var i = 0; i < chkbTable.length; i++) {

                    // Get the region
                    var region = chkbTable[i].getAttribute('value');
                    // Datos = Populations in the region
                    var datos = document.getElementsByName("NewGraphs_" + region + "[]");

                    // typeChart = 'all' | 'one'
                    var typeChart = document.getElementById("typeChart").value;

                    // Separated charts for each population
                    if (typeChart == "all") {

                        // Set <div> size according to the chosen resolution
                        var width = document.getElementById('mapResolution').value;

                        // Size of the piechart
                        //global_Radius = (Math.min(canvas.width, canvas.height) / 25);
                        var maxWidth = Number(document.getElementById('mapResolution').max);
                        var maxHeight = (maxWidth * yMapSize) / xMapSize;
                        global_Radius = (Math.min(canvas.width, canvas.height) / 35) + (Math.min(canvas.width, canvas.height) / 20) * (1 - (Math.min(canvas.width, canvas.height) / (maxHeight)));

                        for (var j = 0, len = datos.length; j < len; j++) {

                            if (($(datos[j]).is(":checked")) &&
                                (datos[j].getAttribute('type') != 'hidden')) {

                                var n = datos[j].value.split(";");
                                // n[0] = STD | n[1] = INV | n[2] = Population | n[3] = STD freq. | n[4] = INV freq. | n[5] = long. | n[6] = lat.

                                // Param 1 >> data: table to fill with all data needed to create the pie-charts
                                // Param 2 >> n: STD alleles, INV alleles, population, STD freq., INV freq.
                                // Param 3 >> population;region
                                arrangeData(data, n, n[2] + ';' + region);
                            }
                        }

                    // A chart for each continent
                    } else {

                        global_Radius = Math.min(canvas.width, canvas.height) / 15;         // Size of the piechart

                        // Start global variables
                        var global_sample = 0;
                        var global_STD = 0;
                        var global_INV = 0;

                        var global_sample_nogenotypes = 0;
                        var global_STD_nogenotypes = 0;
                        var global_INV_nogenotypes = 0;

                        var regPopChckd = 0;    // Counter >> Controls the populations checked in a region

                        for (var j = 0, len = datos.length; j < len; j++) {

                            if (($(datos[j]).is(":checked")) &&
                                (datos[j].getAttribute('type') != 'hidden')) {

                                regPopChckd++;
                                var n = datos[j].value.split(";");

                                if ((Number(n[0]) + Number(n[1])) > 1) {

                                    global_sample += (Number(n[0]) + Number(n[1]));
                                    global_STD += Number(n[0]);
                                    global_INV += Number(n[1]);

                                } else {

                                    global_sample_nogenotypes += 1;
                                    global_STD_nogenotypes += Number(n[0]);
                                    global_INV_nogenotypes += Number(n[1]);

                                }

                            }
                        }

                        if (regPopChckd == 0) { continue; }

                        // Further prepare data
                        if (global_sample_nogenotypes == 0) {

                            // No cal fer res més

                        } else {

                            if (global_sample == 0) {

                                // Només dades sense genotips, promig diferents poblacions
                                global_sample = 1;
                                global_STD = global_STD_nogenotypes / global_sample_nogenotypes;
                                global_INV = global_INV_nogenotypes / global_sample_nogenotypes;

                            } else {

                                // Dades amb i sense genotips, només es mostra dades amb genotips
                                // No cal fer res més

                            }
                        }

                        // Add the data in a table of pies (in order to create the pie-charts)
                        // Considering that: chartData[0] = STD | chartData[1] = INV | chartData[2] = Población
                        var n = [global_STD, global_INV, region];
                        // Param 1 >> data: table to fill with all data needed to create the pie-charts
                        // Param 2 >> n: sum of STD alleles, sum of INV alleles, region
                        // Param 3 >> region;region
                        arrangeData(data, n, region + ';' + region);

                    }

                }
                return data;

            };

            // ARRANGE DATA *********************************************************************
            // Add the data in a table of pies (in order to create the pie-charts)
            // Considering that: chartData[0] = STD alelles | chartData[1] = INV alelles | chartData[2] = Population OR Region
            //                 ( chartData[3] = STD freq. | chartData[4] = INV freq. | chartData[5] = Longitude | chartData[6] = Latitude )
            function arrangeData(dataTable, chartData, popRegion) {

                var dataRow = [];
                var z = 0;                                                          // Index with 2 values: STD >> 0 | INV >> 1
                var piePos = estimatePos(chartData, popRegion);                      // Returns X,Y according to the population/region
                var pieCutPoint = 0;
                var percentage = 0;
                var pieTotal = 0;

                while (z < 2) {                                                     // Values that matter: 2 >> STD & INV (counted in freq. or alleles)

                    if (z == 0) { var pieType = 'STD'; }
                    else if (z == 1) { var pieType = 'INV'; }

                    chartData[2] = chartData[2].charAt(0).toUpperCase() + chartData[2].slice(1);    // Capitalize the 1st letter of population

                    if (typeof chartData[3] !== 'undefined') {
                        // We have freq. values = We're creating pies according to freq. instead of alleles
                        // chartData = [STD alleles, INV alleles, population, STD freq., INV freq.]
                        pieTotal = 1;

                        percentage = Math.round(Number(chartData[z + 3]) * 100);                    // Calculate the percentage value

                        if (z == 0) { pieCutPoint = 0; }                                            // To avoid NaN || To draw the 1st arc at 0 radiants
                        else { pieCutPoint = Number(chartData[z + 2]); }

                        chartData[z] = chartData[z + 3]

                    } else {
                        // We are working with alleles instead of frequencies
                        pieTotal = (Number(chartData[0]) + Number(chartData[1]));

                        if (pieTotal > 0)
                        { percentage = Math.round((Number(chartData[z]) / pieTotal) * 100); }       // Calculate the percentage value
                        else { percentage = 0; }                                                    // To avoid NaN

                        if (!(pieTotal > 0) || (z == 0)) { pieCutPoint = 0; }                       // To avoid NaN || To draw the 1st arc at 0 radiants
                        else { pieCutPoint = Number(chartData[z - 1]) / pieTotal; }
                    }

                    var pieID = chartData[2] + ';' + pieType + ';' + percentage + '%';

                    dataRow = [pieID, piePos[0], piePos[1], Number(chartData[z]), pieTotal, global_Radius, pieCutPoint, hoverColors[z], blurColors[z]];
                    dataTable.push(dataRow);

                    z++;

                    //If working with frequencies instead of alleles:
                    if (typeof chartData[3] !== 'undefined') {
                        // Check the real total (STD freq. + INV freq.) rounded at 3 decimals:
                        var realTotal = Math.round((Number(chartData[z + 1]) + Number(chartData[z + 2])) * 1000) / 1000;
                        // If realTotal is not 1 (100%), there are other alleles: non STD, non INV
                        if ((z == 2) && (realTotal < 1.000)) {                                     // z=2 at the end of the 'while'
                            pieType = 'Other';
                            percentage = Math.round((pieTotal - realTotal) * 100);
                            pieID = chartData[2] + ';' + pieType + ';' + percentage + '%';
                            pieCutPoint = realTotal / pieTotal;
                            dataRow = [pieID, piePos[0], piePos[1], pieTotal - realTotal, pieTotal, global_Radius, pieCutPoint, hoverColors[z], blurColors[z]];
                            dataTable.push(dataRow);
                        }
                    }

                }
            }

            //***********************************************************************************
            // CANVAS SETTINGS **************************************************************

            // Enable resolution slidebar & download button
            document.getElementById('mapResolution').type = 'range';
            document.getElementById('mapResolution').disabled = false;
            document.getElementById('btn_download').disabled = false;

            // Relate canvas as a child of the HTML division <div>
            div = document.getElementById(id);
            div.appendChild(canvas);
            div.style.display = "block";
            div.style.class = "center-block";

            // Set <div> size according to the chosen resolution
            var width = document.getElementById('mapResolution').value;
            var height = Math.floor((width * yMapSize) / xMapSize);
            document.getElementById(id).style.width = width + 'px';
            document.getElementById(id).style.height = height + 'px';

            // Canvas size depending on the <div> size
            canvas.width = width;       //canvas.width = div.offsetWidth;     //canvas.width = div.clientWidth;
            canvas.height = height;     //canvas.height = div.offsetHeight;   //canvas.height = div.clientHeight;

            // Canvas settings
            var canvasOffset = $("#mapCanvas").offset();
            var offsetX = canvasOffset.left;
            var offsetY = canvasOffset.top;

            // Canvas styles
            canvas.style.zIndex = 8;
            canvas.style.position = "relative";
            //canvas.style.border = "1px solid";

            // ELEMENTS SETTINGS ************************************************************
            // Array of pies
            var pies = [];
            // Global variables for the pies. Actually they aren't "global" since they are declared within a function
            var blurColors = ["#2d8bc8", "#96c8e8", "#e6e6e6"];
            var hoverColors = ["#226a98", "#66afdd", "#a6a6a6"];
            var global_Radius = Math.min(canvas.width, canvas.height) / 25;         // Default size of the piechart

            // BACKGROUND & CONTENT *********************************************************
            // Set a world map as background
            var map = new Image();
            map.src = mapName;
            map.onload = function () {

                // Draw the map:
                // a) X/Y position
                // b) X/Y original size (if we want to crop Antarctica for example, we chose a smaller Y than the original)
                // c) X/Y position inside the canvas
                // d) X/Y final size
                ctx.drawImage(map, 0, 0, xMapSize, yMapSize, 0, 0, canvas.width, canvas.height);

                // Draw the legend
                drawLegend();

                // Get data
                var piesData = getData();

                // Creating new pies: ID, posX, posY, Value, Total, Radius, cuPoint, hoverColor, blurColor
                for (var i = 0; i < piesData.length; i++) {
                    pies.push(new pie(piesData[i][0], piesData[i][1], piesData[i][2], piesData[i][3], piesData[i][4], piesData[i][5], piesData[i][6], piesData[i][7], piesData[i][8]));
                }

            }

            // LISTENER *********************************************************************
            // "Turn on" the event listener for mouse move
            $("#mapCanvas").mousemove(handleMouseMove);

        }

        // ESTIMATEPOS **********************************************************************
        // Estimate the position within the map when a population or a region is given
        // Considering that: chartData[0] = STD alelles | chartData[1] = INV alelles | chartData[2] = Population OR Region
        //                 ( chartData[3] = STD freq. | chartData[4] = INV freq. | chartData[5] = Longitude | chartData[6] = Latitude )
        function estimatePos(charData, inputRegion) {

            var position = [];                                              // Values X,Y to return
            var popRegion = inputRegion.split(";");                         // Input region: population;region OR region;region
            var country = "";                                               // Country code, used to determine the X,Y position
            var lng = 0, lat = 0;

            if (!((!charData[5] || !charData[6]) || (charData[5] == 0 && charData[6] == 0))) {     // If there're coordinates and they're not 0,0
                lng = charData[5];
                lat = charData[6];
                position = degToPix(lng, lat);

            } else {                                                        // If there're no coordinates

                if (popRegion[0] == popRegion[1]) {                         // It's a continent chart, not a population chart
                    // switch (popRegion[0].substring(0, 4)) {
                    switch (popRegion[0]) {
                        case ("AfricaAFR"): country = "AFR"; break;
                        case ("AmericaAdmixedAMR"): country = "AMR"; break;
                        case ("AmericaNativesAMN"): country = "AMN"; break;
                        case ("EuropeEUR"): country = "EUR"; break;
                        case ("OceaniaOCE"): country = "OCE"; break;
                        case ("MiddleEastNorthAfricaMENA"): country = "MENA"; break;
                        case ("SouthAsiaSAS"): country = "SAS"; break;
                        case ("EastAsiaEAS"): country = "EAS"; break;
                        case ("CentralAsiaCAS"): country = "CAS"; break;
                         case ("Unknown"): country = "UNN"; break;
                        default:                                            // Let's put the chart in the middle of nowhere
                            country = "randSEA";
                    }

                } else {
                    switch (popRegion[1].substring(0, 4)) {                 // It's a population chart, but the population was not known. Check the continent
                        case ("Afri"): country = "randAFR"; break;
                        case ("Amer"): country = "randAME"; break;
                        case ("Asia"): country = "randASI"; break;
                        case ("Euro"): country = "randEUR"; break;
                        case ("Ocea"): country = "randOCE"; break;
                        case ("Midd"): country = "randMEA"; break;
                        case ("Sout"): country = "randSAN"; break;
                        case ("East"): country = "randASN"; break;
                        default:                                            // Let's put the chart in the middle of nowhere
                            country = "randSEA";
                    }
                }

                // Calculate the position within the map, according to the country
                switch (country) {
                    case ("AFR"):
                        lng = 20.67007;
                        lat = 6.66216;
                        position = degToPix(lng, lat);
                        break;
                    case ("AMR"):
                        lng = -91.000000;
                        lat = 25.000000;
                        position = degToPix(lng, lat);
                        break;
                    case ("AMN"):
                        lng = -73.500000;
                        lat = 8.000000;
                        position = degToPix(lng, lat);
                        break;
                    case ("EUR"):
                        lng = 15.254004;
                        lat = 49.952077;
                        position = degToPix(lng, lat);
                        break;
                    case ("OCE"):
                        lng = 136.475863;
                        lat = -25.823828;
                        position = degToPix(lng, lat);
                        break;
                    case ("MENA"):
                        lng = 43.718752;
                        lat = 33.204207;
                        position = degToPix(lng, lat);
                        break;
                    case ("SAS"):
                        lng = 80.00000;
                        lat = 19.25000;
                        position = degToPix(lng, lat);
                        break;
                    case ("EAS"):
                        lng = 126.533828;
                        lat = 36.231553;
                        position = degToPix(lng, lat);
                        break;
                    case ("CAS"):
                        lng = 85.000000;
                        lat = 55.000000;
                        position = degToPix(lng, lat);
                        break;
                    case ("UNN"):
                        lng = -121.212092;
                        lat = -31.654325;
                        position = degToPix(lng, lat);
                        break;

                    // For those populations where only the continent is known, set the chart in a random position within the continent                                                     
                    case ("randAFR"):
                        var randX = Math.random() * (35.642842 - (-1.447)) + (-1.447);
                        var randY = Math.random() * (27.986405 - (-32.850281)) + (-32.850281);
                        position = degToPix(randX, randY);
                        break;
                    case ("randAME"):
                        var randX = Math.random() * ((-104.696443) - (-42.831626)) + (-42.831626);
                        var randY = Math.random() * (22.497678 - (-53.936437)) + (-53.936437);
                        position = degToPix(randX, randY);
                        break;
                    case ("randASI"):
                        var randX = Math.random() * (153.105562 - 64.537372) + 64.537372;
                        var randY = Math.random() * (67.613852 - 2.905003) + 2.905003;
                        position = degToPix(randX, randY);
                        break;
                    case ("randEUR"):
                        var randX = Math.random() * (25.882627 - (-11.822449)) + (-11.822449);
                        var randY = Math.random() * (64.771295 - 38.336449) + 38.336449;
                        position = degToPix(randX, randY);
                        break;
                    case ("randOCE"):
                        var randX = Math.random() * (177.597531 - 108.163942) + 108.163942;
                        var randY = Math.random() * (-0.229736 - (-46.836981)) + (-46.836981);
                        position = degToPix(randX, randY);
                        break;
                    case ("randMEA"):
                        var randX = Math.random() * (62.907124 - 33.551657) + 33.551657;
                        var randY = Math.random() * (41.335446 - 16.246154) + 16.246154;
                        position = degToPix(randX, randY);
                        break;
                    case ("randSAN"):
                        var randX = Math.random() * (138.412915 - 71.050632) + 71.050632;
                        var randY = Math.random() * (28.460495 - (-6.805739)) + (-6.805739);
                        position = degToPix(randX, randY);
                        break;
                    case ("randASN"):
                        var randX = Math.random() * (170.517517 - 113.506361) + 113.506361;
                        var randY = Math.random() * (58.897487 - 11.273143) + 11.273143;
                        position = degToPix(randX, randY);
                        break;
                    case ("randSEA"):
                        var randX = Math.random() * ((-99.922356) - (-160.422353)) + (-160.422353);
                        var randY = Math.random() * ((-2.140148) - (-55.991292)) + (-55.991292);
                        position = degToPix(randX, randY);
                        break;
                }
            }

            return position;
        }

        // DEGREES TO PIXELS ****************************************************************
        function degToPix(lng, lat) {

            // Transform the coordinates to Radians
            var lngRad = lng * (Math.PI / 180);
            var latRad = lat * (Math.PI / 180);

            // Map's center = [xMapsize/2, yMapSize/2]
            // But it doesn't correspond to world's center when talking in coordinates [0, 0]
            // Thus, we need to set this difference manually:
            var xDiff = -45, yDiff = 58;

            // NATURAL EARTH PROJECTION: we set the coefficients of this projection
            var length = 0, distance = 0;
            switch (true) {
                case (Math.abs(lat) == 0):
                    length = 1;
                    distance = 0;
                    break;
                case (Math.abs(lat) > 0 && Math.abs(lat) <= 5):
                    length = 0.9988;
                    distance = 0.062 * Math.sign(lat);
                    break;
                case (Math.abs(lat) > 5 && Math.abs(lat) <= 10):
                    length = 0.9953;
                    distance = 0.124 * Math.sign(lat);
                    break;
                case (Math.abs(lat) > 10 && Math.abs(lat) <= 15):
                    length = 0.9894;
                    distance = 0.186 * Math.sign(lat);
                    break;
                case (Math.abs(lat) > 15 && Math.abs(lat) <= 20):
                    length = 0.9811;
                    distance = 0.248 * Math.sign(lat);
                    break;
                case (Math.abs(lat) > 20 && Math.abs(lat) <= 25):
                    length = 0.9703;
                    distance = 0.31 * Math.sign(lat);
                    break;
                case (Math.abs(lat) > 25 && Math.abs(lat) <= 30):
                    length = 0.957;
                    distance = 0.372 * Math.sign(lat);
                    break;
                case (Math.abs(lat) > 30 && Math.abs(lat) <= 35):
                    length = 0.9409;
                    distance = 0.434 * Math.sign(lat);
                    break;
                case (Math.abs(lat) > 35 && Math.abs(lat) <= 40):
                    length = 0.9222;
                    distance = 0.4958 * Math.sign(lat);
                    break;
                case (Math.abs(lat) > 40 && Math.abs(lat) <= 45):
                    length = 0.9006;
                    distance = 0.5571 * Math.sign(lat);
                    break;
                case (Math.abs(lat) > 45 && Math.abs(lat) <= 50):
                    length = 0.8763;
                    distance = 0.6176 * Math.sign(lat);
                    break;
                case (Math.abs(lat) > 50 && Math.abs(lat) <= 55):
                    length = 0.8492;
                    distance = 0.6769 * Math.sign(lat);
                    break;
                case (Math.abs(lat) > 55 && Math.abs(lat) <= 60):
                    length = 0.8196;
                    distance = 0.7346 * Math.sign(lat);
                    break;
                case (Math.abs(lat) > 60 && Math.abs(lat) <= 65):
                    length = 0.7874;
                    distance = 0.7903 * Math.sign(lat);
                    break;
                case (Math.abs(lat) > 65 && Math.abs(lat) <= 70):
                    length = 0.7525;
                    distance = 0.8435 * Math.sign(lat);
                    break;
                case (Math.abs(lat) > 70 && Math.abs(lat) <= 75):
                    length = 0.716;
                    distance = 0.8936 * Math.sign(lat);
                    break;
                case (Math.abs(lat) > 75 && Math.abs(lat) <= 80):
                    length = 0.6754;
                    distance = 0.9394 * Math.sign(lat);
                    break;
                case (Math.abs(lat) > 80 && Math.abs(lat) <= 85):
                    length = 0.627;
                    distance = 0.9761 * Math.sign(lat);
                    break;
                case (Math.abs(lat) > 85 && Math.abs(lat) <= 90):
                    length = 0.563;
                    distance = 1 * Math.sign(lat);
                    break;
            }

            // NATURAL EARTH PROJECTION: calculations (in Radians!)
            /* The formulas:
            x = 0.8707 * length * lng;
            y = 0.8707 * 0.52 * distance * PI
            */

            // However, this formulas needed some adjustments to fit better with our map.
            var xProj = 0.8707 * 0.51 * (length + 0.14) * lngRad;           // Note the length variation and the global
            var yProj = -1 * 0.8707 * 0.52 * (distance + 0.035) * Math.PI;  // Note the symbol change (+/-) and the distance variation

            // Next, we set the maximum values: lng=180º, lat=90º
            // Remember taht 180º = PI
            var xMax = 0.8707 * 0.563 * Math.PI;                            // 0.563 = Length when longitude is maximum (180º = PI rad)
            var yMax = 0.8707 * 0.52 * 1 * Math.PI;                         // 1 = Distance when latitude is maximum (90º)

            /* Now we can set the position in our map.
            a) Manage 'Proj' to have values between [-1,+1]
            b) Sum #MapSize/2 to move the center (0,0) to the corner (upper,left)
            c) Consider the difference with world's center and "our center"
            */
            var xPos = ((xProj * (xMapSize / 2)) / xMax) + (xMapSize / 2) + xDiff;
            var yPos = ((yProj * (yMapSize / 2)) / yMax) + (yMapSize / 2) + yDiff;


            /* ALTERNATIVE PROJECTIONS: compromise projections similar to "Natural Earth"
            PROJECTION          Cx          Cy          A       B
            Wagner VI       1.89490     0.94745    -0.5     3
            Kavraisky VII   sqrt(3)/2   1           0       3

            x = Cx * lng * ( A + sqrt( 1 − B*( (lat/PI)^2 ) ) )
            y = Cy * lat

            Where lng=longitude and lat=latitude, theoretically expressed in Radians (1 radian = 180/PI degrees)
            */

            // Finally, we adapt the obtained position to the Canvas size
            var pos = [];
            pos[0] = Math.floor((xPos * canvas.width) / xMapSize);
            pos[1] = Math.floor((yPos * canvas.width) / xMapSize);

            return pos;
        }

        // CLEANNER *************************************************************************
        function clearMap(id) {
            // Check if canvas exists
            if (canvas.getContext && canvas.getContext('2d')) {
                ctx.clearRect(0, 0, canvas.width, canvas.height);       // Clear canvas
            }

            document.getElementById('mapResolution').type = 'hidden';   // Hide resolution slidebar
            document.getElementById('mapResolution').disabled = false;  // Disable resolution slidebar
            document.getElementById('btn_download').disabled = true;    // Disable download button

            $(id).empty();                                              // Clear division
            document.getElementById(id).style.display = 'none';         // Hide <div>
        };

        // DOWNLOADER ***********************************************************************
        function mapDownload() {
            var href = canvas.toDataURL("image/png").replace("image/png", "image/octet-stream"); ;
            window.location.href = href;
        }

        // CANVAS: Create the canvas and its context as global elements *********************
        canvas = document.createElement('canvas');
        ctx = canvas.getContext("2d");
        canvas.id = "mapCanvas";

        // MAP SETTINGS
        var mapName = "img/blank_map.svg";          //var mapName = "img/mercator_projection.svg"
        var xMapSize = 1410;                        //var xMapSize = 1625;
        var yMapSize = 715;                         //var yMapSize = 1050;

    </script>

    <!-- 2016/04 Graphs in map modification ALTERNATIVA END -->
    <!-- **************************************************************************** -->


    <!-- **************************************************************************** -->
	<!-- Script to allow 'expandable' divisions -->
	<script>
		$(document).ready(function(){
			$(".hidden").hide();                                  //Hide all contents 
			$(".section-title").click(function(){                 //Toggle when click title
				$(this).next(".hidden, .grlsection-content").slideToggle(600);
				var title = $(this).html();

				var regExp = /\+/;
				if (title.match(regExp)) {
					title = title.replace('+','-');
					$(this).html(title);
				} else {
					title = title.replace('-','+');
					$(this).html(title);
				}
            });	
		
		    /*
            $('#selectStudy').change(function () {
				var selectStudy = $('#selectStudy').val();
				var parameters = 'selectStudy=' + selectStudy ;
				doAjax('php/refresh_frequency.php', parameters, 'displayResult', 'POST', '0', '<img id=\'load\' src=\'css/img/load.gif\' >');
			}
            */
		});

		function displayResult(text){
			document.getElementById("tableStudy").innerHTML = text;
		}

        /*
        function showEvolInfoResults() {
		    div = document.getElementById('add_evol_info_result');
		    $(div).append('<iframe style="width: 150px;" name="test" id="test"></iframe>');
        }
	    function hideEvolInfoResults() {
		    div = document.getElementById('add_evol_info_result');
		    $(div).empty('');
        }
        */
	</script>
    
    <!-- **************************************************************************** -->
    <!-- Loads Hihgslide JS: image, media and gallery viewer written in JavaScript -->
	<script src="js/highslide_complete.js"></script>
	
    <!-- **************************************************************************** -->
    <script>
	    //document.write('<style type="text/css">');    
	    //document.write('div.domtab div{display:none;}<');
	    //document.write('/s'+'tyle>');                   
	</script>
    
    <!-- **************************************************************************** -->
	<script>    
	    hs.graphicsDir = 'img/highslide_graphics/';
	    hs.outlineType = 'rounded-white';
	    hs.outlineWhileAnimating = true;
	</script>
	
    <!-- **************************************************************************** -->
	<!-- Per Add frequencies without genotypes -->
	<script>
	    function calculateFng(elem) {
	    
	    	var value = elem.value;
    		var id    = elem.id;
    		var chrom = '<?php echo $r["chr"]; ?>';
    		var multi = '';
    		
    		if (chrom == 'chrY') {
    			multi = 1;
    		} else {
    			multi = 2;
    		}
    		
    		if ((id == 'fng_individuals') && (chrom != 'chrX')) {
    		
    			if (document.getElementById('fng_invalleles').value != '') {
    				document.getElementById('fng_invfreq').value = document.getElementById('fng_invalleles').value / (document.getElementById('fng_individuals').value * multi);
    				document.getElementById('fng_stdfreq').value = 1-document.getElementById('fng_invfreq').value;
    			}
    			
    		} else if (id == 'fng_invalleles') {
    		
    			if ((document.getElementById('fng_individuals').value != '') && (chrom != 'chrX')) {
    				document.getElementById('fng_invfreq').value = document.getElementById('fng_invalleles').value / (document.getElementById('fng_individuals').value * multi);
    				document.getElementById('fng_stdfreq').value = 1-document.getElementById('fng_invfreq').value;
    			}
    		
    		} else if (id == 'fng_stdfreq') {
    			document.getElementById('fng_invfreq').value = 1-value;
    			
    			if ((document.getElementById('fng_individuals').value != '') && (chrom != 'chrX')) {
    				document.getElementById('fng_invalleles').value = document.getElementById('fng_individuals').value * multi * document.getElementById('fng_invfreq').value;
    			}
    			
    		} else if (id == 'fng_invfreq') {
    			document.getElementById('fng_stdfreq').value = 1-value;
    			
    			if ((document.getElementById('fng_individuals').value != '') && (chrom != 'chrX')) {
    				document.getElementById('fng_invalleles').value = document.getElementById('fng_individuals').value * multi * document.getElementById('fng_invfreq').value;
    			}
    			
    		} else {} 
	    
	    
	    }
	</script>	

    <!-- **************************************************************************** -->
	<!-- Para el add_validation -->
	<script src="js/autocomplete/jquery.js"></script>
	<script src="js/autocomplete/dimensions.js"></script>
	<script src="js/autocomplete/autocomplete.js"></script>
    <!-- Styles of autocomplete script -->
	<link rel="stylesheet" type="text/css" href="css/autocomplete.css" media="screen" />
	
    <!-- **************************************************************************** -->
    <script>//<![CDATA[ 
	    $(window).load(function(){
	    $(".regChkbox").change(function() {
	        var val = $(this).val();
	      if( $(this).is(":checked") ) {
	    
	        $(":checkbox[class='"+val+"']").attr("checked", true);
	      }
	        else {
		    $(":checkbox[class='"+val+"']").attr("checked", false);
	        }
	    });
	    });//]]>
	</script>

    <!-- **************************************************************************** -->
	<script>
		$(function(){
			setAutoComplete("searchFosmids", "results", "php/autocomplete_fosmids.php?part=");  
        //  setAutoComplete("searchValMethod", "results", "php/fosmids_autocomplete.php?part=");  
		//  ID of input field + ID of the div that will hold the returned data + URL
		});

		$(document).ready(function(){

			$("#includeExperimental").hide();
			$("#includeBioinformatics").hide();
			$("#includeMethod").show();
			$("#includeCompulsory").hide();

			var method=document.getElementById("method");
			$("#method").change(function(){
				if (method.value.match(/PCR|FISH|MLPA/) ) { // experimental
					$("#includeExperimental").show();
					$("#includeBioinformatics").hide();
					$("#includeMethod").hide();
					$("#includeCompulsory").hide();
				} else if (method.value != '') {            // Bioinformatics
					$("#includeExperimental").hide();
					$("#includeBioinformatics").show();
					$("#includeMethod").hide();
					$("#includeCompulsory").show();
				} else if (method.value == "") {            // Non-selected
					$("#includeExperimental").hide();
					$("#includeBioinformatics").hide();
					$("#includeMethod").show();
					$("#includeCompulsory").hide();
				}
            });

		 if ('<?php echo $_GET["o"]; ?>'=='add_val') {
                alert('Validation added succesfully');
            } else if ('<?php echo $_GET["o"]; ?>'=='add_val_inderror') {
                alert('Validation was added successfully but some individuals were not correctly introduced')    
            } else if ('<?php echo $_GET["o"]; ?>'=='add_valError') {
                alert('Validation did not success');
            }

            if ('<?php echo $_GET["o"]; ?>'=='update_val') {
                alert('Validation updated succesfully');
            } else if ('<?php echo $_GET["o"]; ?>'=='add_valError') {
                alert('Validation did not success');
            }
            if ('<?php echo $_GET["o"]; ?>'=='delete_val') {
                alert('Validation deleted succesfully');
            } else if ('<?php echo $_GET["o"]; ?>'=='add_valError') {
                alert('Validation did not success');
            }
        });

        function changeEvolForm(evolInfo,evolInfoForm) {
		    //typo of info: evolInfo.value //"" "evolution_orientation" "evolution_age""evolution_origin"
		    //evolInforForm -> div to append the form
		    $('#evolInfoForm').empty('');
		    if (evolInfo.value == "evolution_orientation") {
            //	if ("<?php echo $species;?>" != '') {
				    $('#evolInfoForm').append(
			    	    "Species <div class='compulsory'>*</div>  <select id='orientation_species' name='orientation_species'>"+
					    "<option value=''>-Select-</option><?php echo $species ?></select> "+
					    "<br>Orientation  <div class='compulsory'>*</div> <select id='orientation_orientation'"+ 
					    "name='orientation_orientation'>"+
					    "<option value=''>-Select-</option><?php echo $orientation ?></select> "+
					    "<br>Method  <div class='compulsory'>*</div> <select id='method_orientation' name='method_orientation'>"+
					    "<option value=''>-Select-</option><?php echo $method_add_val ?></select> "+
					    "<br>Study  <div class='compulsory'>*</div> <select id='source_orientation' name='source_orientation'>"+
					    "<option value=''>-Select-</option><?php echo $research_name ?></select> <a class='highslide-resize' href='php/new_study.php?&t=evolOrient' onclick='return hs.htmlExpand(this, {objectType: \"iframe\", objectHeight:200,  objectWidth:1000 })'> Add a new study</a>");
            //  } else {
            //		$('#evolInfoForm').append("There are no species available to add information");
            //	}
		    }
		    else if (evolInfo.value == "evolution_age") {
			    $('#evolInfoForm').append("Age  <div class='compulsory'>*</div> <input type='text' name='age_age' id='age_age' >  (in years, no thousand separators, only digits)"+
				    "<br>Method  <div class='compulsory'>*</div> <select id='method_age' name='method_age'><option value=''>-Select-</option>"+
				    "<?php echo $method_add_val ?></select> <br>Study  <div class='compulsory'>*</div>  <select id='source_age' name='source_age'>"
				    +"<option value=''>-Select-</option> <?php echo $research_name ?></select> <a class='highslide-resize' href='php/new_study.php?&t=evolAge' onclick='return hs.htmlExpand(this, {objectType: \"iframe\", objectHeight:200,  objectWidth:1000 })'> Add a new study</a>");
		    } else if (evolInfo.value == "evolution_origin") {
			    $('#evolInfoForm').append(
				    "Origin  <div class='compulsory'>*</div> <input type='text' name='origin_origin' id='origin_origin' > "+
				    "<br>Method  <div class='compulsory'>*</div> <select id='method_origin' name='method_origin'>"+
				    "<option value=''>-Select-</option><?php echo $method_add_val ?></select> <br>Study  <div class='compulsory'>*</div> "+
				    "<select id='source_origin' name='source_origin'><option value=''>-Select-</option>"+
				    "<?php echo $research_name ?></select> <a class='highslide-resize' href='php/new_study.php?&t=evolOrigin' onclick='return hs.htmlExpand(this, {objectType: \"iframe\", objectHeight:200,  objectWidth:1000 })'> Add a new study</a>");
		    } else {
			    document.getElementById("evol_type_null").selected=true;
		    }

	    }
	
        function changeFuncForm(functInfo,functEffForm) {
			//typo of info: functInfo.value //"" "eff_genomic" "eff_phenotypic"
			//functEffForm -> div to append the form
			$('#functEffForm').empty('');
			if (functInfo.value == "eff_genomic") {
				$('#functEffForm').append("Gene  <div class='compulsory'>*</div> <select id='gene_func' name='gene_func'>"+
				    "<option value=''>-Select-</option><?php echo $echo_symbols;?></select> <a class='highslide-resize' href='php/new_gene.php?inv_id=<?php echo $id; ?>&t=effGenomic' onclick='return hs.htmlExpand(this, {objectType: \"iframe\", objectHeight:250,  objectWidth:1000 })'>Add new gene</a><br>"+
				    "Effect  <div class='compulsory'>*</div> <input type='text' id='genomic_eff_func' name='genomic_eff_func'><br>"+
				    "Study <div class='compulsory'>*</div> <select id='source_genomic_func' name='source_genomic_func'><option value=''>"+
				    "-Select-</option><?php echo $research_name; ?></select> <a class='highslide-resize' href='php/new_study.php?&t=effGenomic' onclick='return hs.htmlExpand(this, {objectType: \"iframe\", objectHeight:200,  objectWidth:1000 })'>Add new study</a><br>"+
				    "Functional consequences  <div class='compulsory'>*</div> <input type='text' name='conseq_func' id='conseq_func'>");
			} else if (functInfo.value == "eff_phenotypic") {
				$('#functEffForm').append("Effect <div class='compulsory'>*</div> "+
				    "<input type='text' id='phenotypic_eff_func' name='phenotypic_eff_func'><br>"+
				    "Study <div class='compulsory'>*</div> <select id='source_phenotypic_func' name='source_phenotypic_func'>"+
				    "<option value=''>-Select-</option><?php echo $research_name; ?></select> <a class='highslide-resize' href='php/new_study.php?&t=effPhenotypic' onclick='return hs.htmlExpand(this, {objectType: \"iframe\", objectHeight:200,  objectWidth:1000 })'>Add new study</a><br>");
			} else {
			    document.getElementById("effect_type_null").selected=true;
			}
		}
        
		function changeFreqs(study,IDrow,inv_id,population, region){ 
			//IDrow -> id row to change
			//study -> study.value
			var row = document.getElementById(IDrow);

			// crear un ajax que cambie la fila 'row'
			// Create XMLHttpRequest object.
			if (window.XMLHttpRequest)  { xmlhttp = new XMLHttpRequest(); }
			else                        { xmlhttp = new ActiveXObject("Microsoft.XMLHTTP"); }    // For old IE.

			// Create callback function to react when the response from the server is ready.
			xmlhttp.onreadystatechange = function () {
				if (xmlhttp.readyState == 4 && xmlhttp.status == 200)
                    { row.innerHTML = xmlhttp.responseText; }
			}

			// Prepare query for ajax.php
			xmlhttp.open("GET", "php/ajaxChangeFreqs.php?q="+inv_id+"&pop="+population+"&reg="+region+"&stud=" + study.value, true);

			// Send query to ajax.php
			xmlhttp.send();
		}

		function updateTD(type,id) {
			accuracy='';
			if (type=='comments_pred'){
				accuracy=document.getElementById('acc'+id).innerHTML;
			}
			tdElement=document.getElementById(type+id);
			texto=document.getElementById('DIV'+type+id).innerHTML;
			$(tdElement).empty('');
			$(tdElement).append(accuracy+"<textarea id='updatable"+id+"' cols='60'>"+texto+"</textarea><input type='hidden' name='origin' value='"+type+"'><input type='button' value='Update' class='right' onclick=\"ajaxUpdateTD('"+type+"','"+id+"');\">");
			
            //FALTA QUE EL BOTÓN LLAME A UNA FUNCION!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
		}

		function ajaxUpdateTD(origin,id){
			var newtext=document.getElementById('updatable'+id).value;

			var tdElement;
		//  if (origin=='comments') {
        //      tdElement=document.getElementById(id);
        //  } else {
        //      tdElement=document.getElementById(origin+id);
        //  }
			tdElement=document.getElementById(id); 
			if (origin =='inv_bp_origin') {
                tdElement2=document.getElementById('inv_origin'+id);
            }

			// Crear un ajax que cambie el TD correspondiente
			// Create XMLHttpRequest object.
			if (window.XMLHttpRequest) {
                xmlhttp = new XMLHttpRequest();
            }
			else {
                xmlhttp = new ActiveXObject("Microsoft.XMLHTTP");   // For old IE
            }

			// Create callback function to react when the response from the server is ready.
			xmlhttp.onreadystatechange = function () {
				if (xmlhttp.readyState == 4 && xmlhttp.status == 200 && origin =='inv_bp_origin') {
					texto = xmlhttp.responseText;

					tdElement.innerHTML="<div  id='inv_bp_origin"+id+"'>"+texto+"</div><input type='button' value='Edit' class='right' onclick=\"updateTD('inv_bp_origin','"+id+"')\">";
					tdElement2.innerHTML=texto;
				} else if (xmlhttp.readyState == 4 && xmlhttp.status == 200){
                    tdElement.innerHTML = xmlhttp.responseText;
                }
			}

			// Prepare query for ajax.php
			xmlhttp.open("GET", " php/ajaxUpdate_info.php?p="+id+"&up="+newtext+"&or="+origin, true);

			// Send query to ajax.php
			xmlhttp.send();
		}

        function formget(f, url, divID) {
	        var poststr = getFormValues(f);
	        if (divID=='evolution') {
		        var type=document.getElementById("evol_type").value;
		        if (type == "evolution_orientation") {
                    divID='table_evol_orientation_sp';
                } else if (type == "evolution_age") {
                    divID='table_evol_age';
                } else if (type == "evolution_origin") {
                    divID='table_evol_origin';
                }
	        }
	        else if (divID=='functional') {
		        var type=document.getElementById("effect_type").value;
		        if (type == 'eff_genomic') {
			        //para los efectos genomicos, debemos capturar el mecanismo
			        var n=poststr.split("&"); 
			        for(var i in n) {
				        if (n[i].match('gene_func')) {
					        id_gene=n[i].replace("gene_func=",""); 
					        var mechanism=document.getElementById(id_gene+'_mechanism');
					        poststr=poststr+'&mechanism='+mechanism.textContent;
				        } 
			        }
			        divID='functional_effectAjax|'+id_gene;
		        }
		        else if (type == 'eff_phenotypic'){divID='table_phenotypical_effect';}
	        }
	        postData(url, poststr, divID);
        }

        function getFormValues(fobj) {
	        var str = "";
	        var valueArr = null;
	        var val = "";
	        var cmd = "";
	 
	        for(var i = 0;i < fobj.elements.length;i++) {
		        switch(fobj.elements[i].type) {
			        case "text":
				        str += fobj.elements[i].name + "=" + escape(fobj.elements[i].value) + "&";
			        break;
			        case "textarea":
				        str += fobj.elements[i].name + "=" + escape(fobj.elements[i].value) + "&";
			        break;
			        case "select-one":
				        str += fobj.elements[i].name + "=" + fobj.elements[i].options[fobj.elements[i].selectedIndex].value + "&";
			        break;
			        case "checkbox":
				        if ($(fobj.elements[i]).is(":checked")) {  
					        str += fobj.elements[i].name + "=" + escape(fobj.elements[i].value) + "&";
				        }
			        break;
            //      alert(str);
			        case "file":
				        str += fobj.elements[i].name + "=" + escape(fobj.elements[i].value) + "&";
            //      alert(str);
			        break;
			        case "hidden":
				        str += fobj.elements[i].name + "=" + escape(fobj.elements[i].value) + "&";
			        break;

		        }
	        }
	        str = str.substr(0,(str.length - 1));
	        return str;
        }

        // History comments div
        function showDiv(){
           document.getElementById('commentshistoryDiv').style.display = "block";
        }

        function postData(url, parameters, divID){
	        var xmlHttp = AJAX();
	        xmlHttp.onreadystatechange = function(){
		        //if(xmlHttp.readyState > 0 && xmlHttp.readyState < 4) {
		        //  document.getElementById(divID).innerHTML=loadingmessage;
		        //}
		        if (xmlHttp.readyState == 4) {
			        var modify=document.getElementById(divID);
                //	$(modify).append(xmlHttp.responseText);
			        var newText=xmlHttp.responseText;

			    //  error=/^Error:/;alert('-'+newText+'-');
			        if (newText.match(/Error: /)){
                        alert(newText);                         // Si es error, lo muestro con alert
                    } else if (divID.match(/^table/)) {         // Si el DIV es una tabla:
				        if ($('#'+divID).length) {              // Si la tabla ya existe:
					        $('#'+divID).find('tbody:last').append(newText);
				        } else {                                // Si la tabla no existe, la creamos y luego añadimos la fila
					        createTable(divID);
					        $('#'+divID).find('tbody:last').append(newText);
				        }
			        } else if (divID.match('functional_effectAjax')) {
				        id=divID.replace("functional_effectAjax|",""); 
				        $("#"+id).empty();
				        $("#"+id).append(newText);
			        } else {
                        $(modify).append(newText);              // Si no es error, lo añado al DIV correspondiente
                    }
			
		        }
	        }
	        xmlHttp.open("POST", url, true);
	        xmlHttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
	        xmlHttp.setRequestHeader("Content-length", parameters.length);
	        xmlHttp.setRequestHeader("Connection", "close");
	        xmlHttp.send(parameters);
        }

        function AJAX(){
	        var xmlHttp;
	        try{
		        xmlHttp=new XMLHttpRequest();                       // Firefox, Opera 8.0+, Safari
		        return xmlHttp;
	        }
	        catch (e){
		        try{
			        xmlHttp=new ActiveXObject("Msxml2.XMLHTTP");    // Internet Explorer
			        return xmlHttp;
		        }
		        catch (e){
			        try{
				        xmlHttp=new ActiveXObject("Microsoft.XMLHTTP");
				        return xmlHttp;
			        }
			        catch (e){
				        alert("Your browser does not support AJAX!");
				        return false;
			        }
		        }
	        }
 
        }

	    function createTable (id){
		    if (id == 'table_evol_orientation_sp') {
			    $("#evol_orientation_sp").empty();
			    $('#evol_orientation_sp').append('<table width="100%" id="table_evol_orientation_sp"><tr>'+
			        "<td class='title'>Species</td><td class='title'>Orientation</td><td class='title'>Method</td><td class='title'>Study</td>"+
			        "</tr></table>");
		    } else if (id == 'table_evol_age') {
			    $("#evol_age").empty();
			    $('#evol_age').append('<table width="100%" id="table_evol_age"><tr>'+
			        "<td class='title'>Age</td><td class='title' width='37%'>Method</td><td class='title'>Study</td>"+
			        "</tr></table>");
		    } else if (id == 'table_evol_origin') {
			    $("#evol_origin").empty();
			    $('#evol_origin').append('<table width="100%" id="table_evol_origin"><tr>'+
			        "<td class='title'>Species</td><td class='title' width='37%'>Method</td><td class='title'>Study</td>"+
			        "</tr></table>");
		    } else if (id == 'table_phenotypical_effect') {
			    $("#phenotypical_effect").empty();
			    $('#phenotypical_effect').append('<table width="100%" id="table_phenotypical_effect"><tr>'+
			        "<td class='title'>Effect</td><td class='title'>Study</td>"+
			        "</tr></table>");
	        }

	    }

	    function submitNewValidation (f) {      //AJAX
		    returned=validate();
		    if (returned===true) {
			    formget(f, 'php/ajaxAdd_validation.php', 'validationsAjax');
			    // Set the form to 0
			    document.getElementById("research_name_null").selected=true;
			    document.getElementById("method_null").selected=true;
			    document.getElementById("status_null").selected=true;
			    document.getElementById("checked").checked=false;
			    document.getElementById("commentE").value='';
			    document.getElementById("experimental_conditions").value='';
			    document.getElementById("primers").value='';
			    document.getElementById("searchFosmids").value='';
			    document.getElementById("fosmids_results").value='';
			    document.getElementById("commentB").value='';
			    document.getElementById("individuals").value='';
			    document.getElementById("bp1s").value='';
			    document.getElementById("bp1e").value='';
			    document.getElementById("bp2s").value='';
			    document.getElementById("bp2e").value='';
			    document.getElementById("description").value='';
		    }
	    }

	    function submitNewEvolInfo (f) {
		    returned=validate_evol();
		    if (returned===true) {
                //window.location = "php/ajaxAdd_evol_info.php";
			    formget(f, 'php/ajaxAdd_evol_info.php', 'evolution');
			    // Set the form to 0
			    changeEvolForm('new_evol_info','evolInfoForm');
		    }
	    }

	    function submitNewFunctionalInfo (f) {
		    returned=validate_funct();
		    if (returned===true) {
			    formget(f, 'php/ajaxAdd_funct_effect.php', 'functional');
			    // Set the form to 0
			    changeFuncForm('functEff_form','functEffForm');
		    }
	    }

	</script>

</head>


<!-- **************************************************************************** -->
<!-- BODY -->
<!-- **************************************************************************** -->
<body>

    <!-- **************************************************************************** -->
    <!-- PAGE MENU: Print the header banner of InvFEST -->
    <!-- **************************************************************************** --> 
    <?php include('php/echo_menu.php'); ?>
    
    <br/>
    <div id="report">

	<!------ GENERAL INFORMATION ------>
	    <div id="general_info" class="report-section" >
		    <div class="TitleStatic">&nbsp;&nbsp;General information</div>
		    <div class="grlsection-content ContentA">
                <table width='100%'>
	                <tr>
		                <td class="title" width='20%'>Accession</td>
		                <td width='30%'><?php echo $r['name']; ?></td>
		                <td class="title" width='20%'>Region of the inversion</td>
		                <td width='30%'><?php echo $r['chr'].':'.$r['bp1_start'].'-'.$r['bp2_end'];?></td>
	                </tr>
	                <tr>
		                <td class="title">Estimated Inversion Size</td>
		                <td><?php echo number_format($r['size']);?> bp</td>
		                <td class="title">Supporting predictions</td>
		                <td><?php echo $r['num_pred'];?></td>
	                </tr>
	                <tr>
		                <td class="title">Status</td>
		                <td><?php echo $array_status[$r['status']]; ?></td>
		                <td class="title">Number of validations</td>
		                <td><?php echo $r['num_val'];?></td>
	                </tr>
	                <tr>
		                <td class="title">Inverted allele frequency</td>
		                <td><?php 

		                    if ($r['status'] == 'FALSE') {
			                    $r_inv_freq = "<font color='grey'>NA</font>";		
		                    } else if (($r_inv_freq == '') or ($r_inv_freq == 'NA')) {
			                    $r_inv_freq = "<font color='grey'>ND</font>";
		                    } else {}
				
		                    echo $r_inv_freq /*$r['frequency_distribution']*/;

		                ?></td>
		                <td class="title">Mechanism of origin</td>
		                <td id='inv_origin<?php echo $id; ?>'><?php 

		                    if ($r['status'] == 'FALSE') {
			                    $r['origin'] = "<font color='grey'>NA</font>";		
		                    } else if ($r['origin'] == '') {
			                    $r['origin'] = "<font color='grey'>ND</font>";
		                    } else {
			                    $r['origin'] = ucfirst($r['origin']);
		                    }

		                    echo $r['origin'];

		                ?></td>
	                </tr>
	                <tr>
		                <td class="title">Functional effect</td>
		                <td><?php 

		                    echo $array_effects[$r['genomic_effect']];

		                ?></td>
		                <td class="title">Breakpoint 1</td>
		                <td>
                            <?php 
                            if ($r['bp1_between'] == 'TRUE'){
                                echo $r['chr'].':'.$r['bp1_start'].'^'.$r['bp1_end'];
                            }else{
                                echo $r['chr'].':'.$r['bp1_start'].'-'.$r['bp1_end'];
                            }
                            ?>
                        </td>
	                </tr>
	                <tr>
		                <td class="title">Ancestral orientation</td>
		                <td><?php
		
		                    if ($r['status'] == 'FALSE') {
			                    $r['ancestral_orientation'] = "<font color='grey'>NA</font>";		
		                    } else if ($r['ancestral_orientation'] == '') {
			                    $r['ancestral_orientation'] = "<font color='grey'>ND</font>";
		                    } else {
			                    $r['ancestral_orientation'] = ucfirst($r['ancestral_orientation']);
		                    }
				
		                    echo $r['ancestral_orientation'];
		
		                ?></td>
		                <td class="title">Breakpoint 2</td>
		                <td>
                            <?php 
                            if ($r['bp2_between'] == 'TRUE'){
                                echo $r['chr'].':'.$r['bp2_start'].'^'.$r['bp2_end'];
                            }else{
                                echo $r['chr'].':'.$r['bp2_start'].'-'.$r['bp2_end'];
                            }
                            ?>
                        </td>
	                </tr>
	                <tr>
	                	 <td class="title">Complexity</td>
		                <td><?php
		                	if ($r['complexity'] == NULL) {
		                		$r['complexity'] = 'Simple';
		                	}

		                	echo $r['complexity'];
		                 ?></td>
		            </tr> 

	                <?php if (($last_com != '') or ($_SESSION["autentificado"]=='SI')) {
	
	                        echo "
	                <tr>
		                <td class='title'>Comments</td>
		
		                <td colspan=3 id='comments_inv".$id."'><div  id='DIVcomments_inv".$id."'>".$last_com."</div>";
		
		
            	                if ($_SESSION["autentificado"]=='SI') {
        		        		
        		                echo "<input type='button' class='right' value='Edit' onclick=\"updateTD('comments_inv','".$id."')\" />";
			                #echo "<div id='commentshistoryDiv'  style='display:none;'> <hr>$comments_history_inversion</div>";
			
			                #echo "<input type='button' name = 'answer' class='right' value='History' onclick='showDiv()' />";
			                #echo "<a input type='button' vaule = 'History' id='displayText' onClick='javascript:toggle();'>show</a> "; 
			                echo "<div id='toggleText'  style='display:none;'> <hr>$comments_history_inversion</div>";
			                echo "<input type='button' value='History' class='right' onClick=javascript:toggle();>";

			                #echo "<td colspan=3 id='comments_inv".$id."'><div  id='DIVcomments_inv".$id."'>".$comments_history_inversion."</div>";

            	                }		
		
		                    echo "
                        </td>
	                </tr>";

	                } ?>

                    <script> 
                        function toggle() {
	                        var ele = document.getElementById("toggleText");
	                        var text = document.getElementById("displayText");
	                        if(ele.style.display == "block") {
    		                        ele.style.display = "none";
		                        text.innerHTML = "show";
  	                        } else {
		                        ele.style.display = "block";
		                        text.innerHTML = "hide";
	                        }
                        } 
                    </script>

                </table>


<?php
/*
//			if ($r['name'] != "" || $r['name'] != NULL) {
				echo '<p class="field"><strong>Name: </strong>'. $r['name'].'</p>';
//			}
//			if ($r['size'] != "" || $r['size'] != NULL) {
				echo '<p class="field"><strong>Size: </strong> '.$r['size'].'</p>';
//			}
//			if ($r['status'] != "" || $r['status'] != NULL) {
				echo '<p class="field"><strong>Status: </strong> '.$r['status'].'</p>';
//			}
			echo '<p class="field"><strong>Global frequency: </strong> campo?? </p>';
			echo '<p class="field"><strong>Functional effect: </strong> campo?? </p>';
			echo '<p class="field"><strong>Ancestral orientation: </strong> campo?? </p>';
//			if ($r['comment'] != "" || $r['comment'] != NULL) {
				echo '<p class="field"><strong>Comment: </strong> '.$r['comment'].'</p>';
//			}
			echo '</div>
			<div class="right">';
			echo '<p class="field"><strong>Inversion position: </strong> '.$r['chr'].': '.$r['range_start'].'-'.$r['range_end'].' </p>';
			echo '<p class="field"><strong>Supporting predictions: </strong> campo?? </p>';
			echo '<p class="field"><strong>Number of validations: </strong> campo?? </p>';
//			if ($r['origin'] != "" || $r['origin'] != NULL) {
				echo '<p class="field"><strong>Mechanism of Origin: </strong>'.$r['origin'].'</p>';
//			}
			echo '<p class="field"><strong>Breakpoint1: </strong> '.$r['chr'].': '.$r['bp1_start'].'-'.$r['bp1_end'].' </p>';
			echo '<p class="field"><strong>Breakpoint2: </strong> '.$r['chr'].': '.$r['bp2_start'].'-'.$r['bp2_end'].' </p>';

//			if ($r['chr'] != "" || $r['chr'] != NULL) {
//				echo '<p class="field"><strong>Chromosome: </strong> '.$r['chr'].'</p>';
//			}
//			if ($r['range_start'] != "" || $r['range_start'] != NULL) {
//				echo '<p class="field"><strong>Range start: </strong> '.$r['range_start'].'</p>';
//			}
//			if ($r['range_end'] != "" || $r['range_end'] != NULL) {
//				echo '<p class="field"><strong>Range end: </strong> '.$r['range_end'].'</p>';
//			}
//			if ($r['type'] != "" || $r['type'] != NULL) {
//				echo '<p class="field"><strong>Type: </strong> '.$r['type'].'</p>';
//			}
//			if ($r['detected_amount'] != "" || $r['detected_amount'] != NULL) {
//				echo '<p class="field"><strong>Detected ammount: </strong> '.$r['detected_amount'].'</p>';
//			}
//			if ($ind_pred['c'] != "0" ) {
//				echo '<p class="field"><strong>Predicted individuals: </strong> '.$ind_pred['c'].'</p>';
//			}
//			if ($ind_val['c'] != "0") {
//				echo '<p class="field"><strong>Validated individuals: </strong> '.$ind_val['c'].'</p>';
//			}
			echo '</div>';
*/
?>

            <!--
                <div class="right bkp">
				    <h4>Breakpoint 1</h4>
				    <p class="field"><strong>Start: </strong><?php echo $r['bp1_start'];?></p>
				    <p class="field"><strong>End: </strong><?php echo $r['bp1_end'];?></p>
				    <br>
				    <h4>Breakpoint 2</h4>
				    <p class="field"><strong>Start: </strong><?php echo $r['bp2_start'];?></p>
				    <p class="field"><strong>End: </strong><?php echo $r['bp2_end'];?></p>
				    <br>
				    <?php
				        if ($r['definition_method'] != "" || $r['definition_method'] != NULL) {
					        echo '<p class="field"><strong>Definition method: </strong>'.$r['definition_method'].'</p>';
				        }
				        if ($r['seg_dup_id'] != "" || $r['seg_dup_id'] != NULL) {
					        echo '<p class="field"><strong>Segmental duplications: </strong>'.$r['seg_dup_id'].'</p>';
				        }
				    ?>
			    </div>
            -->
			    <div style="clear:both;"></div>
		    </div>	
	    </div>

        <!------- REGION MAP ------->
        <div id="region_map" class="report-section" >
            <!-- 2016/04 START -->
            <!-- <div class="TitleStatic">Region map </div> -->
            <div class="section-title TitleA">- Region map</div>
            <!-- 2016/04 END -->

            <div class="grlsection-content ContentA">
            <?php if($db == 'INVFEST-DB-PUBLIC'){ ?>
                    <a href="http://genome.ucsc.edu/cgi-bin/hgTracks?hgS_doOtherUser=submit&hgS_otherUserName=InvFEST&hgS_otherUserSessionName=InvFEST&db=hg18&position=<?php echo $r['chr'];?>:<?php echo $start_image; #$pos['inicio']?>-<?php echo $end_image; #$pos['fin']?>" target="_blank"><img id="region" src="image_db_public.pl<?php echo $perlurl; ?>" /> </a>  <!-- http://genome.ucsc.edu/cgi-bin/hgTracks?db=hg18&position=<?php echo $r['chr'];?>%3A<?php echo $start_image; #$pos['inicio'];?>-<?php echo $end_image; #$pos['fin'];?>&Submit=submit -->
            <?php
                }
                if($db == 'INVFEST-DB') {
            ?>
                    <a href="http://genome.ucsc.edu/cgi-bin/hgTracks?hgS_doOtherUser=submit&hgS_otherUserName=InvFEST&hgS_otherUserSessionName=InvFEST&db=hg18&position=<?php echo $r['chr'];?>:<?php echo $start_image; #$pos['inicio']?>-<?php echo $end_image; #$pos['fin']?>" target="_blank"><img id="region" src="image_db_private.pl<?php echo $perlurl; ?>" /> </a>  <!-- http://genome.ucsc.edu/cgi-bin/hgTracks?db=hg18&position=<?php echo $r['chr'];?>%3A<?php echo $start_image; #$pos['inicio']?>-<?php echo $end_image; #$pos['fin']?>&Submit=submit -->
            <?php } ?>
    
        </div>

            <!-- 2016/04 START -->
            </div>
            <!-- 2016/04 END -->
        </div>

	    <div id="predictions" class="report-section" >
        <!--- PREDICTIONS -->
		    <div class="section-title TitleA">+ Predictions </div>
		    <div class="hidden">
		        <div class="grlsection-content ContentA">
		        <?php if ($echo_predictions != "") { ?>
		        <!--
                    <table id="validation_table">
			            <thead>
			                <tr>
			                    <td>Study</td>	<td>Breakpoint1</td>	<td>Breakpoint2</td>
				                <td>Status</td>
				                <td>Comment</td>	<td>Accuracy</td>	<td>Fosmids</td>	<td>Individuals</td>
			                </tr>
			            </thead>
			            <tbody>
		        -->
                <?php       echo $echo_predictions; ?>
		        <!--
                        </tbody>
			        </table>
		        -->
		        <?php } else { echo 'No predictions are found'; } ?>
		        </div>	
		    </div>
	    </div>
	    <div id="validations" class="report-section" >
        <!------- VALIDATIONS ------->
            <div class="section-title TitleA">+ Validation and genotyping  </div>
		    <?php if ($_GET['o']!='add_val' && $_GET['o']!='add_valError'){ ?>
		    <div class="hidden">
		    <?php } ?>
		        <div class="grlsection-content ContentA">
		            <div id="validationsAjax">
		    <?php
		            if ($echo_validations != "") {
		            /*	echo '<table id="validation_table">
			            <thead>
			            <tr>
				            <td>Research Name</td>	<td>Method</td>	<td>Status</td>	';
			            if ($_SESSION["autentificado"]=='SI') {
				            echo '<td>Experimental conditions</td>	<td>Primers</td>	';
			            }
			            echo'<td>Comment</td>	<td>Fosmids</td>	<td>Individuals</td>
			            </tr>
			            </thead>
			                <tbody>
				                '.$echo_validations.'
			                </tbody>
			            </table>
			        ';
			        */
			            echo $echo_validations;
		            } else  { 
                        echo 'No validations are found';
                    }
		        echo "</div>"; // End validationsAjax
                if ($_SESSION["autentificado"]=='SI' && ($r['status']!='Withdrawn' && $r['status']!='withdrawn')) {
            ?>
                <!--
                    <p><a class="highslide-resize" href="php/new_validation.php?&q=<?php echo $id ?>" 
                        onclick="return hs.htmlExpand(this, {objectType: 'iframe', objectHeight:300 })">
                        Add a new validation
                    </a></p>
                -->
			        <div class="section-title TitleB">+ New validation</div>
			        <div class="hidden">
			            <div class="grlsection-content ContentA"> 

                        <!--<form name="new_validation" method="post" enctype="multipart/form-data"  >-->
                            <form name="new_validation" method="post" action="php/add_validation.php" 
                                onsubmit="return validate()" enctype="multipart/form-data" >
                            <!--<onsubmit="submitNewValidation(this.form)">-->
	                            Study Name 
                                <div class="compulsory">*</div> 
                                <select id="research_name" name="research_name" >
                                    <option value="" id='research_name_null'>-Select-</option>
		                            <?php echo $research_name ?>
                                </select>
                                <p style="display:inline-block"><a class="highslide-resize" href="php/new_study.php?&t=val" 
                                    onclick="return hs.htmlExpand(this, {objectType: 'iframe', objectHeight:200,  objectWidth:1000 })">
                                    Add a new study
                                </a></p><br>
	                            Method 
                                <div class="compulsory">*</div> 
                                <select name="method" id="method" >
                                    <option value="" id='method_null'>-Select-</option>
		                            <?php echo $method_add_val ?>
                                </select><br>
	                            Status <div class="compulsory">*</div> 
                                <select name="status" id="status" >
                                    <option value="" id='status_null'>-Select-</option>
		                            <?php echo $status_add_val ?>
                                </select><br>
	                            Force status <input name="checked" id="checked" type="checkbox" value="yes" /><br> <!-- ELIMINAR!!! -->
	                            Comment <textarea rows="1" cols="68" name="commentE" id="commentE" type="text" /></textarea><br>

	                            <div id="validation" class="report-section" >
		                            <div class="section-title TitleB">
                                        + Validation details 
                                    <!--<div id="includeCompulsory"><div class="compulsory">*</div></div>-->:
		                            </div>
		                            <div class="hidden" >
		                                <div class="grlsection-content ContentA">
			                                <div id="includeExperimental">
				                                Experimental conditions <input name="experimental_conditions" id="experimental_conditions" type="text" /><br>
				                                Primers <input name="primers" id="primers" type="text" /><br>
			                                </div>
			                                <div id="includeBioinformatics">
			                                    <p id="auto" style="display: inline-block"> 
				                                    <label>Fosmids <!--<div class="compulsory">*</div>--> </label>
				                                    <input type="text" id="searchFosmids" name="searchFosmids" />
			                                    </p><br>
				                                Results <!--<div class="compulsory">*</div>--> 
                                                <input name="fosmids_results" id="fosmids_results" type="text" /><br>
				                                Comment <textarea rows="1" cols="61" name="commentB" id="commentB" type="text" /></textarea><br>
			                                </div>
			                                <div id="includeMethod">
				                                Please select a method
			                                </div>
		                                </div>
		                            <?php if ($_GET['o']!='add_val'){ ?>
		                            </div>
		                            <?php } ?>
	                            </div>

	                            <div id="individuals" class="report-section" >
		                            <div class="section-title TitleB">+ Individuals:</div>
		                            <div class="hidden">
		                                <div class="grlsection-content ContentA">
			                                Individuals <input type="file" name="individuals" id="individuals" /><br>
		                                </div>
		                            </div>
	                            </div>
	                            <div id="nogenotypes" class="report-section" >
		                            <div class="section-title TitleB">+ Frequency without genotypes:</div>
		                            <div class="hidden">
		                                <div class="grlsection-content ContentA">
			                                <font color='red'>
                                                Please be aware that this information will be displayed in the Frequency section of the Inversion report, but the following will not be available: Hardy-Weinberg test and genotype file for download. Also, data will not be averaged with other studies of the same population.
                                            </font><br><br>
			                                Population 
                                            <select id='fng_population' name='fng_population'>
				                                <option value='null'>-Select-</option>
				                                <?php echo $fng_population; ?>
			                                </select><br>
			                                Analyzed individuals <input name="fng_individuals" id="fng_individuals" type="text" onchange="calculateFng(this)"/><br>
			                                Inverted alleles <input name="fng_invalleles" id="fng_invalleles" type="text" onchange="calculateFng(this)" /><br>
			                                Standard frequency <input name="fng_stdfreq" id="fng_stdfreq" type="text" onchange="calculateFng(this)" /><br>
			                                Inverted frequency <input name="fng_invfreq" id="fng_invfreq" type="text" onchange="calculateFng(this)" /><br>
		                                </div>
		                            </div>
	                            </div>
	                            <div id="addBreakpoints" class="report-section" >
		                            <div class="section-title TitleB">+ Manually curated breakpoints:</div>
		                            <div class="hidden">
		                                <div class="grlsection-content ContentA">
			                                Breakpoint 1 start <input name="bp1s" id="bp1s" type="text" /><br>
                                            Breakpoint 1 end <input name="bp1e" id="bp1e" type="text" /><br>
                                            Breakpoint 1 between start-end <input type="checkbox" id="between_bp1" name="between_bp1" /><br />
                                            Breakpoint 2 start <input name="bp2s" id="bp2s" type="text" /><br>
                                            Breakpoint 2 end <input name="bp2e" id="bp2e" type="text" /><br>
                                            Breakpoint 2 between start-end <input type="checkbox" id="between_bp2" name="between_bp2" /><br />
                                            Description <textarea rows="1" cols="40" name="description" id="description" type="text" /></textarea><br>
		                                </div>
		                            </div>
	                            </div>

	                            <input type="hidden" name="inv_id" id="inv_id" value="<?php echo $id ?>" />
	                            <input type="hidden" name="chr" id="chr" value="<?php echo $r['chr'] ?>" />
	                            <input type="submit" value="Add validation" />
	                        <!--<input type="button" value="Add validation"  onclick="submitNewValidation(this.form);" />-->
	                            <input type="reset" value="Clear" /><br><br>
                            </form>

			            </div>
			        </div>

        <?php   } ?>
		        </div>
		    </div>	
	    </div>
	    <div id="frequency" class="report-section" >
        <!------- FREQUENCY ----->

            <!-- If there're frequencies to show, the section is opened -->
            <?php if ($echo_frequency != "") { ?>
		    <div class="section-title TitleA">- Frequency </div>
		    <!-- <div class="hidden"> -->
            
            <?php } else { ?>
            <div class="section-title TitleA">+ Frequency </div>
		    <div class="hidden">
            <?php } ?>

                    <div class="grlsection-content ContentA">
		    <?php if ($echo_frequency != "") { ?>

                    <!-- 2016/04 START Graphs in map modification -->
                    
                        <!-- Frequency map (geochart) -->
		            <?php echo $NewGraphs; ?>
                    
                        <!-- Frequency table -->
                    <?php echo $echo_frequency;?>
                        
                        <!-- Execute the script to show the map by default -->
                        <script async>isCanvasSupported('divMap');</script>

                    <!-- 2016/04 END Graphs in map modification -->

		    <?php } else {
                echo 'No frequency studied';
            } ?>
		
                </div>
            <?php if ($echo_frequency == "") { ?>
		    </div>
            <?php } ?>
	    </div>
	    <div id="breakpoints" class="report-section" >
    <!-- BREAKPOINTS  -->
		    <div class="section-title TitleA">+ Breakpoints </div>
		    <div class="hidden">
		        <div class="grlsection-content ContentA">
			        <table width='100%'>
				        <tr>
                            <td class='title' width='18%'>Breakpoint 1</td>
                            <td>
                            <?php 
                            if ($r['bp1_between'] == 'TRUE'){
                                echo $r['chr'].':'.$r['bp1_start'].'^'.$r['bp1_end'];
                            }else{
                                echo $r['chr'].':'.$r['bp1_start'].'-'.$r['bp1_end'];
                            }
                            ?>
                        </td>
                            <td class='title' width='18%'>Breakpoint 2
                            <td>
                            <?php 
                            if ($r['bp2_between'] == 'TRUE'){
                                echo $r['chr'].':'.$r['bp2_start'].'^'.$r['bp2_end'];
                            }else{
                                echo $r['chr'].':'.$r['bp2_start'].'-'.$r['bp2_end'];
                            }
                            ?>
                        </td>
                        </tr>
 				
 			    <?php if (($r['studyname'] !='') or ($_SESSION["autentificado"]=='SI')) { ?>
                	    <tr><td class='title'>Study</td><td colspan="3"><?php echo $r['studyname']; ?></td></tr> 
 			    <?php }

       	              if (($r['description'] !='') or ($_SESSION["autentificado"]=='SI')) { ?>
			            <tr><td class='title'>Description</td><td colspan="3"><?php echo ucfirst($r['description']);?></td></tr>
			    <?php }
 				
 			          if (($r['definition_method'] !='') or ($_SESSION["autentificado"]=='SI')) { ?>
                	    <tr><td class='title'>Definition method</td><td colspan="3"><?php echo $array_definitionmethod[$r['definition_method']];?></td></tr>
			    <?php }

 			          if (($r['origin'] !='') or ($_SESSION["autentificado"]=='SI')) { ?> 
			            <tr><td class='title'>Mechanism of origin</td>
				            <td id='inv_bp_origin<?php echo $id?>' colspan="3">
                                <div id='DIVinv_bp_origin<?php echo $id?>'><?php echo ucfirst($r['origin']);?></div>
                          <?php if ($_SESSION["autentificado"]=='SI') { ?>	
                                <input type='button' value='Edit' class='right' onclick="updateTD('inv_bp_origin','<?php echo $id?>')"><?php } ?>
                            </td>
			            </tr>
			    <?php }

			    if (($r['origin'] =='') or ($_SESSION["autentificado"]=='SI') or ($r['origin'] =='ND')) { ?> 
                	    <tr><td class='title'>Mechanism of origin<br>(predicted by BreakSeq)</td><td colspan="3"><?php echo $r['Mech'];?></td>
                    </table>
			    <?php }

			    if (($r['Flexibility'] !='') or ($_SESSION["autentificado"]=='SI')) { 
                	    echo "<table width='100%'>"?>
                        <tr>
                            <td class='title' width="18%">Flexibility</td>
                            <td><?php echo round($r['Flexibility'],2);?></td>
			    <?php }

			    if (($r['GC'] !='') or ($_SESSION["autentificado"]=='SI')) { ?> 
                            <td class='title' width="18%">GC content </td>
                            <td ><?php echo $r['GC'];?></td>
			    <?php }

			    if (($r['Stability'] !='') or ($_SESSION["autentificado"]=='SI')) { ?> 
                            <td class='title' width="18%">Stability</td>
                            <td><?php echo round($r['Stability'],2);?></td>
                        </tr>
                    </table>
			    <?php }

	            if ($last_com_bp != '' or $_SESSION["autentificado"]=='SI') {
	                echo "
	                <table width='100%'>
                        <tr>
		                    <td class='title' width='18%'>Comments</td>
		                    <td colspan=3 id='comments_bp".$id."'>
                                <div  id='DIVcomments_bp".$id."'>".$last_com_bp."</div>";
            	            if ($_SESSION["autentificado"]=='SI') {
        		                echo "<input type='button' class='right' value='Edit' onclick=\"updateTD('comments_bp','".$id."')\" />";
			                    echo "<div id='togglebphistory'  style='display:none;'> <hr>$comments_history_bp</div>";
			                    echo "<input type='button' value='History' class='right' onClick=javascript:toggle2();>";
            	            }
		              echo "</td>
	                    </tr>";
	            }
            


                if (($bp_seq_features !='') or ($TE_features !='') or ($IR_features !='') or ($_SESSION["autentificado"]=='SI')) {

                    $rowspann="2";
                    if (($TE_features =='') and ($IR_features =='') and ($bp_seq_features !='')) { $rowspann="2"; }
                    if (($TE_features !='') and ($IR_features =='') and ($bp_seq_features =='')) { $rowspann="2"; }
                    if (($TE_features =='') and ($IR_features !='') and ($bp_seq_features =='')) { $rowspann="2"; }
                    if (($TE_features !='') and ($IR_features =='') and ($bp_seq_features !='')) { $rowspann="3"; }
                    if (($TE_features !='') and ($IR_features !='') and ($bp_seq_features =='')) { $rowspann="3"; }
                    if (($TE_features =='') and ($IR_features !='') and ($bp_seq_features !='')) { $rowspann="3"; }
                    if (($TE_features !='') and ($IR_features !='') and ($bp_seq_features !='')) { $rowspann="4"; }
                    
                    if ($_SESSION["autentificado"]=='SI') { $rowspann++; }    
                    
                    echo "
                        <tr>
                            <td  class='title' rowspan=$rowspann>
                                <b>Sequence features</b><br>";
                        if ($_SESSION["autentificado"]=='SI') {
                            echo "<input type=\"button\" value=\"Add\" onclick=\"Show_Div(features_div)\" />
                            </td>
                        </tr>"
                        ; }
                        else { 
                            echo "</td>
                        </tr>"
                        ; }
            ?>
                       
                        </tr>
            <?php 
                    if(($bp_seq_features !='')){
                        echo "
                        <tr>
                            <td colspan='5'><b>Segmental duplications:</b><br/>";
                        echo "
                                <table width='100%'>
                                    <tr>
                                        <td class='title'>Position SD1</td>
                                        <td class='title'>Size (bp)</td>
                                        <td class='title'>Position SD2</td>
                                        <td class='title'>Size (bp)</td>
                                        <td class='title'>Identity</td>
                                        <td class='title'>Relative orientation</td>
                                    </tr>";
                        echo $bp_seq_features;
                        echo "  </table>";
                    }
                    #TE
                    if(($TE_features !='')){
                        echo "
                                <tr>
                                    <td colspan='5'><b>Transposable elements:</b><br/>";
                        echo "      <table width='100%'>
                                        <tr>
                                            <td class='title'>Type</td>
                                            <td class='title'>Position TE1</td>
                                            <td class='title'>Size (bp)</td>
                                            <td class='title'>Position TE2</td>
                                            <td class='title'>Size (bp)</td>
                                            <td class='title'>Identity</td>
                                            <td class='title'>Relative orientation</td>
                                        </tr>";
                        echo $TE_features;
                        echo "      </table>";
                    }
                    #IR
                    if(($IR_features !='')){
                        echo "<tr>
                                <td colspan='5'>
                                    <b>Inverted repeats:</b><br/>";
                        echo "      <table width='100%'>
                                        <tr>
                                            <td class='title'>Position IR1</td>
                                            <td class='title'>Size (bp)</td>
                                            <td class='title'>Position IR2</td>
                                            <td class='title'>Size (bp)</td>
                                            <td class='title'>Identity</td>
                                            <td class='title'>Relative orientation</td>
                                        </tr>";
                        echo $IR_features;
                        echo "      </table>";
                    }

                    if ($_SESSION["autentificado"]=='SI') { echo "<tr><td colspan='5'>"; }
            ?>
                    <div id="features_div" class="content" style="display: none;">
                        <form name="myForm" action="php/insert_bp_features.php" method='post'>
                            Type: 
                            <select id="select_type" name = 'type' onchange="changeTest()">
                            <!--     <option value="SD">Segmental duplication</option> -->
                                <option value="IR">Inverted repeat</option>
                                <option value="TE">Transposable element</option>
                            </select>
                            <div id="subtype" class="content" style="display: none;">
                                Subtype: <input type="text" name="TE_subtype"><br>
                            </div>
                            <!--
                            $type = explode("_",$_POST['type']);
                            $type_SD = $type[0];
                            $type_IR = $type[1];
                            $type_TE = $type[2];
                            -->
                            <div id="subfeautes" class="content">
                                <input type="hidden" name="inversionid" value="<?php echo $id; ?>">
                                Position 1: <input type="text" name="position1"> (chrn:start-end)<br>
                                <!--Size (bp): <input type="text" name="size1"><br>//-->
                                Position 2: <input type="text" name="position2"> (chrn:start-end)<br>
                                <!--Size (bp): <input type="text" name="size2"><br>//-->
                                Identity: <input type="text" name="identity"><br>
                                Orientation (+/-): <input type="text" name="relativeorientation">
                            </div>
                            <input type='submit' class='right' value="Submit" name="submit">
                        </form>
                    </div>
            
            <?php echo"
                    </table>
                </td>
            </tr>"; }
            ?>
            
            

            <!-- Show Add sequence features -->
            <script>
                function Show_Div(Div_id) {
                    if (false == $(Div_id).is(':visible')) {
                        $(Div_id).show(250);
                    }
                    else {
                        $(Div_id).hide(250);
                    }
                }
                function hide_div(Div_id) {
                        $(Div_id).hide(250);
                }
                function changeTest() {
                    if(document.getElementById("select_type").value == "TE"){   
                        
                            Show_Div(subtype);}
                    else{ hide_div(subtype);}
                }
            </script>


	
			        </table>
		        </div>	
		    </div>	
	    </div>
    <!--<div id="segmental_duplication" class="report-section" >-->
        <!------- SEGMENTAL DUPLICATION ------>
        <!--<div class="section-title">Segmental duplication</div>-->
        <!--
		    <div class="hidden">
		        <div class="grlsection-content ContentA">
			        <table id="validation_table">
			            <thead>
			                <tr>
				                <td>Chromosome</td>
                                <td>Chromosome start</td>
				                <td>Chromosome end</td>
                                <td>Strand</td>
				                <td>Other start</td>
                                <td>Other end</td>
			                </tr>
			            </thead>
			            <tbody>
	             <?php echo $segmental_duplication;?>
			            </tbody>
			        </table>
		        </div>
		    </div>	
	    </div><br>
        -->

	    <div id="evolutionary_history" class="report-section" >
        <!------- EVOLUTIONARY HISTORY ------->
		    <div class="section-title TitleA">+ Evolutionary history </div>
        <?php if ($_GET['o']!='add_evol') { ?>
		    <div class="hidden">
	    <?php } ?>
	    	    <div class="grlsection-content ContentA">
		    	    <table width='100%'>
			    	    <tr>
				    	    <td class='title' width='18%'>Ancestral allele</td>
                            <td width='32%'>
                        <?php echo ucfirst($r['ancestral_orientation']); ?></td>
					        <td class='title' width='18%'>Age</td>
                            <td width='32%'>
                        <?php echo $echo_summary_age; //$r['age'] ?></td>
                        </tr>
				        <tr>
					        <td class='title' width='18%'>Derived allele</td><td width='32%'>
                          <?php 
						        if(ucfirst($r['ancestral_orientation'])=='Standard') { echo 'Inverted'; }
                                elseif(ucfirst($r['ancestral_orientation'])=='Inverted') { echo 'Standard'; } 
                                elseif(ucfirst($r['ancestral_orientation'])!='Standard' and ucfirst($r['ancestral_orientation'])!='Inverted') { echo 'NA'; }
                                elseif(ucfirst($r['ancestral_orientation'])=="<font color='grey'>NA</font>") { echo '<font color="grey">NA</font>'; }
                                elseif(ucfirst($r['ancestral_orientation'])=="<font color='grey'>ND</font>") { echo '<font color="grey">ND</font>'; }
                                #else { echo $r['ancestral_orientation']; }
                          ?></td>
					        <td class='title' width='18%'>Origin</td>
                            <td width='32%'>
                          <?php if ($r['evo_origin'] != '') {echo ucfirst($r['evo_origin']); }
                                else {echo '<font color="grey">ND</font>'; }
                          ?></td>
				        </tr>

	      <?php if (($last_com_eh != '') or ($_SESSION["autentificado"]=='SI')) {
	              echo "<tr>
		                    <td class='title'>Comments</td>
		                    <td colspan=3 id='comments_eh".$id."'>
                                <div  id='DIVcomments_eh".$id."'>".$last_com_eh."</div>";
		
            	    if ($_SESSION["autentificado"]=='SI') {
        		          echo "<input type='button' class='right' value='Edit' onclick=\"updateTD('comments_eh','".$id."')\" />";
			              echo "<div id='toggleehhistory'  style='display:none;'> <hr>$comments_history_eh</div>";
			              echo "<input type='button' value='History' class='right' onClick=javascript:toggle3();>";
            	    }
		
		              echo "</td>
	                    </tr>";
	            }
	      ?>

                        <script> 
                            function toggle2() {
	                            var ele = document.getElementById("togglebphistory");
	                            var text = document.getElementById("displayText");
	                            if(ele.style.display == "block") {
    		                            ele.style.display = "none";
		                            text.innerHTML = "show";
  	                            } else {
		                            ele.style.display = "block";
		                            text.innerHTML = "hide";
	                            }
                            } 
                            </script>	
                            <script> 
                            function toggle3() {
	                            var ele = document.getElementById("toggleehhistory");
	                            var text = document.getElementById("displayText");
	                            if(ele.style.display == "block") {
    		                            ele.style.display = "none";
		                            text.innerHTML = "show";
  	                            } else {
		                            ele.style.display = "block";
		                            text.innerHTML = "hide";
	                            }
                            }
                        </script>
				
			        </table>
			
                    <div class="report-section" >
			            <div class="section-title TitleA">+ Orientation in other species</div>
			            <div class="hidden">
			                <div class="grlsection-content ContentB">
			            <?php if ($echo_evolution_orientation!='') { ?>
				                <table width='100%' id='table_evol_orientation_sp'>  
					                <tr>
                                        <td class='title' width='25%'>Species</td>
                                        <td class='title' width='25%'>Orientation</td>
					                    <td class='title' width='25%'>Method</td>
                                        <td class='title' width='25%'>Study</td></tr>
					              <?php echo $echo_evolution_orientation; ?> 
                                <!-- EJEMPLO:
					                <tr>
                                        <td><em>Pan troglodytes</em></td>
                                        <td>STD</td><td>Genome sequencing</td>
                                        <td>Caceres et al.</td>
                                    </tr>
					                <tr>
                                        <td><em>Macaca multatta</em></td>
                                        <td>STD</td><td>FISH</td>
                                        <td>Caceres et al.</td>
                                    </tr>
                                -->
				                </table>
			            <?php } else {
                                echo "<div id='evol_orientation_sp'>Not defined</div>";
                              }
                        ?>
			                </div>
			            </div>
			        </div>
                    <div class="report-section" >
			            <div class="section-title TitleA">+ Age</div>
			            <div class="hidden">
			                <div class="grlsection-content ContentB">
			            <?php if ($echo_evolution_age!='') { ?>
				                <table width='100%' id='table_evol_age'>
					                <tr>
                                        <td class='title' width='20%'>Age</td>
                                        <td class='title' width='40%'>Method</td>
                                        <td class='title' width='40%'>Study</td>
                                    </tr>
					          <?php echo $echo_evolution_age; ?> 
                                <!-- EJEMPLO: 
					                <tr>
                                        <td>1 My</td><td>Divergence with chimpanzee</td>
                                        <td>Caceres et al.</td>
                                    </tr>
					                <tr>
                                        <td>0,5 My</td>
                                        <td>Polymorphism data</td>
                                        <td>Caceres et al.</td>
                                    </tr>
                                -->
				                </table>
			            <?php } else { 
                                echo "<div id='evol_age'>Not defined</div>";
                              }
                        ?>
			                </div>
			            </div>
			        </div>
                    <div class="report-section" >
			            <div class="section-title TitleA">+ Origin</div>
			            <div class="hidden">
			                <div class="grlsection-content ContentB">
			            <?php if ($echo_evolution_origin!='') { ?>
				                <table width='100%' id='table_evol_origin'>
					                <tr>
                                        <td class='title' width='20%'>Origin</td>
                                        <td class='title' width='40%'>Method</td>
                                        <td class='title' width='40%'>Study</td>
                                    </tr>
					          <?php echo $echo_evolution_origin; ?>
                                <!-- EJEMPLO: 
					                <tr>
                                        <td>Monophiletic</td>
                                        <td>Xxxx</td>
                                        <td>Caceres et al.</td>
                                    </tr>
                                -->
				                </table>
			            <?php } else {
                                echo "<div id='evol_origin'>Not defined</div>";
                              }
                        ?>
			                </div>
			            </div>
			        </div>
                <!--<div class="report-section" >
			            <div class="section-title TitleA">+ Nucleotide variation</div>
			            <div class="hidden">
			                <div class="grlsection-content ContentB">Not defined</div>
			            </div>
			        </div>
                    <div class="report-section" >
			            <div class="section-title TitleA">+ Selection tests</div>
			            <div class="hidden">
			                <div class="grlsection-content ContentB">Not defined</div>
			            </div>
			        </div>
                -->


		    <!-- Modify evolutionary information ....................................................................  -->
            <?php if ($_SESSION["autentificado"]=='SI' && ($r['status']!='Withdrawn' && $r['status']!='withdrawn')) { ?>
			        <div class="report-section" >
                        <div class="section-title TitleB">+ Add evolutionary information</div>
			            <div class="hidden">
			                <div class="grlsection-content ContentA"> 

                            <!--<form name="new_evol_info" method="post" action="php/add_evol_info.php" onsubmit="showEvolInfoResults()" onsubmit="return validate_evol()" enctype="multipart/form-data" target="test" >-->
                                <form name="new_evol_info" method="post" enctype="multipart/form-data"  >
	                                Type of information 
                                    <div class="compulsory">*</div> 
                                    <select id="evol_type" name="evol_type"  onchange="changeEvolForm(this,'evolInfoForm')"> 
                                        <option value="" id='evol_type_null'>-Select-</option>
 	                                    <option value="evolution_orientation" >Orientation in other species</option>
		                                <option value="evolution_age" >Age</option>
		                                <option value="evolution_origin" >Origin</option>
                                    </select>

	                                <div id="evolInfoForm"></div>
                                    <input type="hidden" name="inv_id" value="<?php echo $id?>" />
	                            <!--<input type="submit" value="Add information" />-->
	                                <input type="button" value="Add Evolutionary Information"  onclick="submitNewEvolInfo(this.form);" />
	                                <input type="reset" value="Clear" /><br><br>
                                </form>

    	                        <div id='add_evol_info_result'></div>
			                </div>
			            </div>
                    </div>
            <?php } ?>

                <!--
                    <table id="validation_table">
                        <thead>
			                <tr>
				                <td>Name</td>
                                <td>Gender</td>
				                <td>Orientation</td>
			                </tr>
			            </thead>
			            <tbody>
                    <?php echo $evolution;?>
			            </tbody>
			        </table>
                -->

		    <?php if ($_GET['o']!='add_evol') { ?>
		        </div>
		    <?php } ?>
		    </div>
	    </div>
	    <div id="functional_effect" class="report-section" >
        <!------- FUNCTIONAL EFFECT ------->
		    <div class="section-title TitleA">+ Functional effects </div>
		    <div class="hidden">
		        <div class="grlsection-content ContentA">
			        <div id='functional_effectAjax'>
			    <?php 
			        if ($echo_functional_effect != '') {
				        echo $echo_functional_effect;
			        } else {
				        echo "No genes disrupted by the inversion breakpoints";
			        }
			    ?>
			        </div>
                    <div class="report-section" >
			            <div class="section-title TitleA">+ Inversion phenotypical effects</div>
			            <div class="hidden">
			                <div class="grlsection-content ContentB">
			            <?php if ($echo_phenotypical_effect!='') { ?>
				                <table width='100%' id='table_phenotypical_effect'> 
                                <!-- EJEMPLO: 
					                <tr>
                                        <td class='title'>Effect</td>
                                        <td class='title'>Study</td>
                                    </tr>
					                <tr>
                                        <td>Associated to increase fertility</td>
                                        <td>Steffanson et al</td>
                                    </tr>
                                -->
					            <?php echo $echo_phenotypical_effect; ?>
				                </table>
			            <?php } else {
                                echo "<div id='phenotypical_effect'>Not defined</div>";
                              }
                        ?>
			                </div>
			            </div>
			        </div>

			    <!-- Modify functional effect ....................................................................  -->
                <?php if ($_SESSION["autentificado"]=='SI' && ($r['status']!='Withdrawn' && $r['status']!='withdrawn')) { ?>
				    <div class="report-section" >
                        <div class="section-title TitleB">+ Add functional effects</div>
				        <div class="hidden">
				            <div class="grlsection-content ContentA"> 
                            <!--<form name="functEff_form" method="post" action="php/add_functional_effect.php" onsubmit="return validate_funct()" enctype="multipart/form-data"  >-->
                                <form name="functEff_form" method="post" enctype="multipart/form-data"  >
		                            Type of effect 
                                    <div class="compulsory">*</div> 
                                    <select id="effect_type" name="effect_type"  onchange="changeFuncForm(this,'functEffForm')"> 
                                        <option value="" selected='selected' id='effect_type_null'>-Select-</option>
			                            <option value="eff_genomic" >Genomic</option>
			                            <option value="eff_phenotypic" >Phenotypic</option>
			                        </select>
		                            <div id="functEffForm"></div>
		                            <input type="hidden" name="inv_id" value="<?echo $id?>" />
		                        <!--<input type="submit" value="Add information" />-->
		                        <!--<input type='button' value="Add information"  onclick="formget(this.form, 'php/ajaxAdd_funct_effect.php', 'function')" />-->
                                <input type="hidden" name="inv_id_pheno" id="inv_id_pheno" value="<?php echo $id ?>" />
		                            <input type="button" value="Add information"  onclick="submitNewFunctionalInfo(this.form);" />
		                            <input type="reset" value="Clear" /><br><br>
	                            </form>
            		            <div id='add_evol_info_result'></div>
				            </div>
		                </div>
                    </div>
			    <?php } ?>

                <!--<table id="validation_table">
			            <thead>
			                <tr>
				                <td>Gene relation</td>	
                                <td>Functional effect</td>
				                <td>Symbol</td>
			                </tr>
			            </thead>
			            <tbody>
			        <?php echo $genomic_effect;?>
			            </tbody>
			        </table>
                -->
		        </div>	
		    </div>	
	    </div>


    <?php // <!------- REPORT HISTORY ------->
        echo '
	    <div id="inversion_history" class="report-section" >';
    //	if ($r['status']!='Withdrawn' && $r['status']!='withdrawn') {
            echo '<div class="section-title TitleA">+ Report history </div>
				    <div class="hidden">';
    //  } else {
    //	    echo '
    //                  <div class="section-title">- Report history </div>';
    //  }
                    echo'					
	                    <div class="grlsection-content ContentA">
                            '.$history.$bp_history;
    //  echo "
    //                      <a href=\"php/breakpoints_history.php?id=$id\" onclick=\"return hs.htmlExpand(this, {objectType: 'iframe' })\" >See the historial</a>";
    /*	echo '
				            <div class="section-title TitleA">- Breakpoints history </div>

				            <div class="grlsection-content ContentB">
                                <table >
					                <thead>
					                    <tr>
						                    <td class="title">Breakpoint1</td>
                                            <td class="title">Breakpoint2</td>
                                            <td class="title">Description</td>
						                    <td class="title">Definition method</td>
                                            <td class="title">Date</td>
					                    </tr>
					                </thead>
					                <tbody>
						                '.$bp_history.'
					                </tbody>
					            </table> 
				            </div>';
    */
                echo '
                        </div>
			        </div>';

    //  if ($r['status']!='Withdrawn' && $r['status']!='withdrawn') {
		    echo '
                </div>';
    //  } 
    //	echo '<br />';
    //	}
    ?>

    <?php if ($_SESSION["autentificado"]=='SI' && ($r['status']!='Withdrawn' && $r['status']!='withdrawn')) { ?>
        <?php
            $sql_inversion_status="select distinct status from inversions where status is not null order by status;";
            $result_inversion_status = mysql_query($sql_inversion_status) or die("Query fail: " . mysql_error());
            while($thisrow = mysql_fetch_array($result_inversion_status)) {
	            $inversion_status_option.="<option value=\"".$thisrow["status"]."\">".$array_status_no_format[$thisrow["status"]]."</option>";
            }
        ?>
	            <div id="advanced_edition" class="report-section" >
                <!-- ---- ADVANCED EDITION ---- -->
		            <div class="section-title TitleB">+ Advanced inversion edition </div>
		            <div class="hidden">
		                <div class="grlsection-content ContentA">
			                <div class="section-title TitleB">+ Merge current inversion with another</div>
			                <div class="hidden">
			                    <div class="grlsection-content ContentA">
                                 <?php 
                                     echo "$best_merge";
                                ?>
                                    <br />
				                    <form name="merge" action="php/add_merge_inversions.php" method="post" >
                                        <table class="merge_table">
                                             <thead>
                                              <td class="merge_table"></td>
                                                <?php foreach($array_inv_to_merge as $inv_name_merge) { ?>
                                                    <th class="merge_table">
                                                        <?php echo $inv_name_merge;} ?>
                                                    </th>
                                             </thead>
                                            <?php 
                                            echo "$inversions_merge_checkbox";
                                            echo "$inversions_name_checkbox";
                                            echo "$inversions_mech_checkbox";
                                            echo "$inversions_breakpoints_checkbox";
                                            echo "$inversions_evolutionary_checkbox";
                                            echo "$inversions_functional_checkbox";
                                            echo "$inversions_comments_checkbox";
                                            ?>
                                            <tr></tr>
                                        </table>
                                        <br /><br />
                                        <?php echo "<b>Select the following additional information for the new inversion</b>"; ?>
                                        <br /><br />
                                        Status: <select name="status">
                                            <?php echo $inversion_status_option ?></select>
                                        <br><br>
                                        <!--<br>Mechanism of origin: <input type="text"name="mechanism_bp"></input><br><br>-->
                                        <br>
                                        <input type="submit" value="Merge" />
                                        <br><br>
                                    </form>
			                    </div>
			                </div>
			                <br />
			                <div class="section-title TitleB">+ Split current inversion in two others</div>
			                <div class="hidden">
			                    <div class="grlsection-content ContentA">
                                <!--<a href="php/split_inversions.php?q=<?echo $id?>"  onclick="return hs.htmlExpand(this, {objectType: 'iframe' })">Split this inversion</a>-->
				                <form name="split_validation" method="post" action="php/add_split_inversions.php">
					                <table>
					                    <thead>
					                        <td>Predictions</td>
					                        <td>New Inversion 1</td>
					                        <td>New Inversion 2</td>
					                    </thead>
					        <?php
					            if ($predictions == "" || $predictions == NULL) {
					              echo "<tr><td colspan=\"3\">No predictions found</td></tr>";
					            } else { echo $predictions; }
					        ?>
					                    <tr></tr>
					                    <thead>
					                        <td>Validations</td>
					                        <td>New Inversion 1</td>
					                        <td>New Inversion 2</td>
					                    </thead>
					        <?php
					            if ($validations == "" || $validations == NULL) {
					              echo "<tr><td colspan=\"3\">No validations found</td></tr>";
					            } else { echo $validations; }
					        ?>
					                    <thead>
					                        <td>Status</td>
					                        <td>New Inversion 1</td>
					                        <td>New Inversion 2</td>
					                    </thead>
                                        <tr>
                                            <td></td>
                                            <td> 
                                                <select name="status1">
                                                <?php echo $inversion_status_option?>
                                                </select>
                                            </td>
                                            <td>
                                                <select name="status2">
                                                <?php echo $inversion_status_option ?>
                                                </select>
                                            </td>
                                        </tr>
					                </table>
					                <input type="submit" value="&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Split&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;" />
					                <input type="hidden" name="inv_id" value="<?php echo $id ?>" />
					                <input type="reset" value="Clear" /><br><br>
				                </form>
			                </div>
			            </div>
		                <br>
		            </div>
		        </div>
	        </div>
	        <br />
    <?php } ?>

		    <div class="clear"></div>
        </div> <!--end Report-->


        <!-- **************************************************************************** -->
        <!-- FOOT OF THE PAGE -->
        <!-- **************************************************************************** -->
        <div id="foot">
            <?php include('php/footer.php'); ?>
        </div>

</div> <!-- Closes the Wrapper's divison opened at 'echo_menu.php' -->

</body>
</html>
