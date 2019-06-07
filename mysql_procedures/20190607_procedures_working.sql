-- MySQL dump 10.13  Distrib 5.7.26, for Linux (x86_64)
--
-- Host: localhost    Database: INVFEST-DB
-- ------------------------------------------------------
-- Server version	5.5.60-MariaDB

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Dumping routines for database 'INVFEST-DB'
--
/*!50003 DROP FUNCTION IF EXISTS `add_BP` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8 */ ;
/*!50003 SET character_set_results = utf8 */ ;
/*!50003 SET collation_connection  = utf8_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = '' */ ;
DELIMITER ;;
CREATE DEFINER=`invfest_admin`@`localhost` FUNCTION `add_BP`(validation_id_val INT,`inversion_id_val` int, chr_val  VARCHAR(255), bp1s_val INT, bp1e_val INT, bp2s_val INT, bp2e_val INT, description_val  TEXT, user_id_val INT) RETURNS varchar(255) CHARSET latin1
    SQL SECURITY INVOKER
BEGIN
	
 
	DECLARE same_BP_val  INT DEFAULT 0;
	DECLARE new_BP_id_val  INT DEFAULT 0;
	DECLARE next_date  DATETIME ;

	DECLARE previous_value_val text;
	DECLARE newer_value_val text;
	DECLARE task_val text;



	
	
	
	
DECLARE result VARCHAR(255);





									
									SET next_date = CURRENT_TIMESTAMP();
		
									INSERT INTO breakpoints	(inv_id, chr, bp1_start, bp1_end, bp2_start, bp2_end, definition_method, description, date)
												VALUES (inversion_id_val, chr_val, bp1s_val, bp1e_val,  bp2s_val, bp2e_val, "manual curation", description_val, next_date);	
		
									SELECT LAST_INSERT_ID() INTO new_BP_id_val;
									SET task_val = CONCAT('INSERT new breakpoints of inv ', inversion_id_val);
									CALL  save_log(user_id_val, task_val, "none", new_BP_id_val);


									CALL  get_inv_gene_realtion(new_BP_id_val);
									SET task_val = CONCAT('INSERT genomic_effect in breakpoints ', new_BP_id_val, 'of inv ' , inversion_id_val);
									CALL  save_log(user_id_val, task_val, '', '');

									CALL  get_SD_in_BP (new_BP_id_val);
									SET task_val = CONCAT('INSERT SD_in_BP in breakpoints ', new_BP_id_val, 'of inv ' , inversion_id_val);
									CALL  save_log(user_id_val, task_val, '', '');


									if validation_id_val != "NA" THEN
										SELECT bp_id INTO previous_value_val FROM validation WHERE id = validation_id_val;
										UPDATE validation SET bp_id = new_BP_id_val WHERE id = validation_id_val;
										SELECT bp_id INTO newer_value_val FROM validation WHERE id = validation_id_val;
										SET task_val = CONCAT('UPDATE bp_id of validation ', validation_id_val);
										CALL  save_log(user_id_val, task_val, previous_value_val, newer_value_val);
									END IF;

									SELECT CONCAT('range_start: ',range_start,', range_end: ', range_end,', size: ', size) INTO previous_value_val FROM inversions WHERE id = inversion_id_val;

									UPDATE inversions SET range_start = bp1s_val, range_end =  bp2e_val, size = (bp2s_val -  bp1e_val)-1 WHERE id = inversion_id_val;	

									SELECT CONCAT('range_start: ',range_start,', range_end: ', range_end,', size: ', size) INTO newer_value_val FROM inversions WHERE id = inversion_id_val;

									SET task_val = CONCAT('UPDATE range and size of inv ',inversion_id_val);

									CALL  save_log(user_id_val, task_val, previous_value_val, newer_value_val);









UPDATE predictions SET accuracy = "prediction outside of the current location of the inversion breakpoints"
WHERE	chr = chr_val  AND inv_id = inversion_id_val
AND (
CASE research_name 
WHEN "Martinez-Fundichely et al. 2013" THEN 
														NOT (
																						( bp1s_val BETWEEN BP1s AND BP1e) 
																						OR 
																						( bp1e_val BETWEEN BP1s AND BP1e )
																						OR
																						( (bp1s_val <= BP1s)  AND (bp1e_val >= BP1e) )
														) 
										OR NOT(
																						( bp2s_val BETWEEN BP2s AND BP2e) 
																						OR 
																						( bp2e_val BETWEEN BP2s AND BP2e )
																						OR
																						( (bp2s_val <= BP2s) AND (bp2e_val >= BP2e))
																						
														)
ELSE
														NOT (
																						( bp1s_val BETWEEN RBP1s AND RBP1e) 
																						OR 
																						( bp1e_val BETWEEN RBP1s AND RBP1e )
																						OR
																						( (bp1s_val <= RBP1s)  AND (bp1e_val >= RBP1e) )
														) 
										OR NOT(
																						(bp2s_val BETWEEN RBP2s AND RBP2e) 
																						OR 
																						( bp2e_val BETWEEN RBP2s AND RBP2e )
																						OR
																						( (bp2s_val <= RBP2s) AND (bp2e_val >= RBP2e))
																						
														)
END
);

UPDATE predictions SET accuracy = NULL
WHERE	chr = chr_val  AND inv_id = inversion_id_val AND accuracy = 'prediction outside of the current location of the inversion breakpoints'
AND (
CASE research_name 
WHEN "Martinez-Fundichely et al. 2013" THEN 
														 (
																						( bp1s_val BETWEEN BP1s AND BP1e) 
																						OR 
																						( bp1e_val BETWEEN BP1s AND BP1e )
																						OR
																						( (bp1s_val <= BP1s)  AND (bp1e_val >= BP1e) )
														) 
										AND (
																						( bp2s_val BETWEEN BP2s AND BP2e) 
																						OR 
																						( bp2e_val BETWEEN BP2s AND BP2e )
																						OR
																						( (bp2s_val <= BP2s) AND (bp2e_val >= BP2e))
																						
														)
ELSE
														 (
																						( bp1s_val BETWEEN RBP1s AND RBP1e) 
																						OR 
																						( bp1e_val BETWEEN RBP1s AND RBP1e )
																						OR
																						( (bp1s_val <= RBP1s)  AND (bp1e_val >= RBP1e) )
														) 
										AND (
																						(bp2s_val BETWEEN RBP2s AND RBP2e) 
																						OR 
																						( bp2e_val BETWEEN RBP2s AND RBP2e )
																						OR
																						( (bp2s_val <= RBP2s) AND (bp2e_val >= RBP2e))
																						
														)
END
);



SELECT IF(SUM(S.cont) > 0, 'YES', 'NO')  INTO result
FROM
(
SELECT 
CASE research_name 
			WHEN "Martinez-Fundichely et al. 2013" THEN 
							IF (
														(
																						( bp1s_val BETWEEN BP1s AND BP1e) 
																						OR 
																						( bp1e_val BETWEEN BP1s AND BP1e )
																						OR
																						( (bp1s_val <= BP1s)  AND (bp1e_val >= BP1e) )
														) 
										AND(
																						( bp2s_val BETWEEN BP2s AND BP2e) 
																						OR 
																						( bp2e_val BETWEEN BP2s AND BP2e )
																						OR
																						( (bp2s_val <= BP2s) AND (bp2e_val >= BP2e))
																						
														),
									1, 	0
									)
			ELSE
						IF (				
														(
																						( bp1s_val BETWEEN RBP1s AND RBP1e) 
																						OR 
																						( bp1e_val BETWEEN RBP1s AND RBP1e )
																						OR
																						( (bp1s_val <= RBP1s)  AND (bp1e_val >= RBP1e) )
														) 
										AND(
																						( bp2s_val BETWEEN RBP2s AND RBP2e) 
																						OR 
																						( bp2e_val BETWEEN RBP2s AND RBP2e )
																						OR
																						( (bp2s_val <= RBP2s) AND (bp2e_val >= RBP2e))
																						
														),
								1, 	0
								)
END AS cont
FROM predictions
WHERE	chr = chr_val  AND inv_id = inversion_id_val
) AS S;

RETURN result;

END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP FUNCTION IF EXISTS `add_validation` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8 */ ;
/*!50003 SET character_set_results = utf8 */ ;
/*!50003 SET collation_connection  = utf8_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = '' */ ;
DELIMITER ;;
CREATE DEFINER=`invfest_admin`@`localhost` FUNCTION `add_validation`(`inv_id_val` INT(11), `research_name_val` VARCHAR(255), `validation_val` VARCHAR(255), `valiadtion_method_val` VARCHAR(255), `PCRconditions_val` VARCHAR(255), `primer_val` VARCHAR(255), `validation_comment_val` TEXT , `checked_val` VARCHAR(255), user_id_val INT) RETURNS int(11)
    SQL SECURITY INVOKER
BEGIN

 
  
  DECLARE pred_id_val INT;
	 DECLARE pred_research_name_val VARCHAR(255);

	DECLARE inversion_status_val  VARCHAR(255);
  DECLARE predition_status_val  VARCHAR(255);
	DECLARE current_inv_status_val  VARCHAR(255);
	DECLARE val_id  VARCHAR(255);


  DECLARE no_more_rows_inversion BOOLEAN;
  DECLARE loop_cntr_inversion INT DEFAULT 0;
  DECLARE num_rows_inversion INT DEFAULT 0; 


	DECLARE previous_value_val text;
	DECLARE newer_value_val text;
	DECLARE task_val text;


  DECLARE prediction_cur CURSOR FOR
    SELECT
        research_id, research_name
    FROM predictions
		WHERE inv_id = inv_id_val;


	DECLARE CONTINUE HANDLER FOR NOT FOUND
    SET no_more_rows_inversion = TRUE;



	


	INSERT INTO validation	(research_name, inv_id, method, status, experimental_conditions, primers, comment, checked)
											VALUES (research_name_val, inv_id_val, valiadtion_method_val, validation_val, PCRconditions_val, primer_val, validation_comment_val, checked_val);	

	SET val_id = LAST_INSERT_ID() ;

	SET task_val = CONCAT('INSERT new validation to inv ',inv_id_val);
	CALL  save_log(user_id_val, task_val, "none", val_id);


	SELECT validation_amount INTO previous_value_val FROM inversions WHERE id = inv_id_val;
	UPDATE inversions SET validation_amount = validation_amount+1 WHERE id = inv_id_val;
	SELECT validation_amount INTO newer_value_val FROM inversions WHERE id = inv_id_val;
	SET task_val = CONCAT('UPDATE validation_amount of inv ',inv_id_val);
	CALL  save_log(user_id_val, task_val, previous_value_val, newer_value_val);


IF validation_val != 'BP curation' and validation_val != 'genotyping' THEN
		
SET inversion_status_val = validation_val;



		SELECT status INTO current_inv_status_val
										FROM inversions
										WHERE id = inv_id_val;
									
							IF inversion_status_val = 'TRUE' OR inversion_status_val = 'FALSE' THEN
									
									IF current_inv_status_val IS NULL OR ((current_inv_status_val != 'TRUE') AND (current_inv_status_val != 'FALSE')) THEN
										SELECT status INTO previous_value_val FROM inversions WHERE id = inv_id_val;
										UPDATE inversions SET status = inversion_status_val WHERE id = inv_id_val;
										SELECT status INTO newer_value_val FROM inversions WHERE id = inv_id_val;
										SET task_val = CONCAT('UPDATE status of inv ',inv_id_val);
										CALL  save_log(user_id_val, task_val, previous_value_val, newer_value_val);

									ELSEIF (current_inv_status_val != inversion_status_val) AND ((current_inv_status_val = 'TRUE') OR (current_inv_status_val = 'FALSE') )THEN
											SELECT status INTO previous_value_val FROM inversions WHERE id = inv_id_val;
											UPDATE inversions SET status = 'Ambiguous' WHERE id = inv_id_val;
											SELECT status INTO newer_value_val FROM inversions WHERE id = inv_id_val;
											SET task_val = CONCAT('UPDATE status of inv ',inv_id_val);
											CALL  save_log(user_id_val, task_val, previous_value_val, newer_value_val);
									ELSE
											UPDATE inversions SET status = inversion_status_val WHERE id = inv_id_val;
									END IF;
							END IF;


	
	SELECT CONCAT('on_', status) INTO predition_status_val FROM inversions WHERE id = inv_id_val ;

OPEN prediction_cur;
	SELECT FOUND_ROWS() INTO num_rows_inversion;
WHILE loop_cntr_inversion < num_rows_inversion DO 
									
									FETCH  prediction_cur
									INTO	pred_id_val, pred_research_name_val;
										SELECT status INTO previous_value_val FROM predictions WHERE research_id = pred_id_val AND research_name =  pred_research_name_val AND inv_id = inv_id_val;
										UPDATE predictions SET status =  predition_status_val WHERE research_id = pred_id_val AND research_name =  pred_research_name_val AND inv_id = inv_id_val;
										SELECT status INTO newer_value_val FROM predictions WHERE research_id = pred_id_val AND research_name =  pred_research_name_val AND inv_id = inv_id_val;
										SET task_val = CONCAT('UPDATE status of predictions ',pred_id_val, ';', pred_research_name_val);
										CALL  save_log(user_id_val, task_val, previous_value_val, newer_value_val);	
										
SET loop_cntr_inversion = loop_cntr_inversion + 1; 									
END WHILE ;

END IF;
RETURN  val_id ;

END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP FUNCTION IF EXISTS `inv_frequency` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8 */ ;
/*!50003 SET character_set_results = utf8 */ ;
/*!50003 SET collation_connection  = utf8_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = '' */ ;
DELIMITER ;;
CREATE DEFINER=`invfest_admin`@`localhost` FUNCTION `inv_frequency`(inv_id_val INT, population_val VARCHAR(255), population_id_val VARCHAR (255),  region_val VARCHAR(255), study_val VARCHAR(255)) RETURNS varchar(255) CHARSET latin1
    SQL SECURITY INVOKER
BEGIN

DECLARE inv_frequecy VARCHAR(255) DEFAULT '0';

DECLARE chrm VARCHAR(255);
SELECT chr INTO chrm FROM inversions WHERE id = inv_id_val;

IF chrm = 'chrY' THEN
		SELECT CONCAT_WS(';', last.indiv, last.inv_alle, last.inv_freq, "NA")
INTO inv_frequecy
FROM(
SELECT CAST(ending.indiv AS CHAR) AS indiv , CAST(ending.alle_inv AS CHAR) AS inv_alle, CAST((ending.alle_std) AS CHAR) AS std_alle,
				CAST(ending.alle_inv/(ending.total_allel) AS CHAR) AS inv_freq,"can not be compute" AS HW

FROM(

SELECT SUM( IF(sumary.pat_ascendent = 0,
											sumary.son_male + sumary.unrelated,
											IF(sumary.pat_ascendent != 0,
																sumary.pat_ascendent + sumary.unrelated,0))
					) AS indiv,
				
				SUM( IF(sumary.pat_ascendent = 0,
											sumary.son_male_total_allele + sumary.unrelated_total_allele,
											IF(sumary.pat_ascendent != 0,
																sumary.pat_total_allele + sumary.unrelated_total_allele, 0))
					) AS total_allel,

			 SUM( IF(sumary.pat_ascendent = 0,
											sumary.son_male_inv_allele + sumary.unrelated_inv_allele,
											IF(sumary.pat_ascendent != 0,
																sumary.pat_inv_allele + sumary.unrelated_inv_allele, 0))
					) AS alle_inv,
					
			 SUM( IF(sumary.pat_ascendent = 0,
											sumary.son_male_del_allele + sumary.unrelated_del_allele,
											IF(sumary.pat_ascendent != 0,
																sumary.pat_del_allele + sumary.unrelated_del_allele, 0))
					) AS alle_del,

			 SUM( IF(sumary.pat_ascendent = 0,
											sumary.son_male_std_allele + sumary.unrelated_std_allele,
											IF(sumary.pat_ascendent != 0,
																sumary.pat_std_allele + sumary.unrelated_std_allele, 0))
					) AS alle_std

FROM(
	SELECT t.name,t.chr, t.population, t.family, t.relationship, 
		
		SUM(IF(t.relationship IN ('father'), t.male_person, 0)) AS pat_ascendent,
		SUM(IF(t.relationship IN ('father'), t.male_total_allele, 0)) AS pat_total_allele,
		SUM(IF(t.relationship IN ('father'), t.male_inv_allele, 0)) AS pat_inv_allele,
		SUM(IF(t.relationship IN ('father'), t.male_del_allele, 0)) AS pat_del_allele,
		SUM(IF(t.relationship IN ('father'), t.male_std_allele, 0)) AS pat_std_allele,

		SUM(IF(t.relationship IN ('child') AND t.gender = "Male" , t.male_person, 0)) AS son_male,
		SUM(IF(t.relationship IN ('child') AND t.gender = "Male" , t.male_total_allele, 0)) AS son_male_total_allele,
		SUM(IF(t.relationship IN ('child') AND t.gender = "Male" , t.male_inv_allele, 0)) AS son_male_inv_allele,
		SUM(IF(t.relationship IN ('child') AND t.gender = "Male" , t.male_del_allele, 0)) AS son_male_del_allele,
		SUM(IF(t.relationship IN ('child') AND t.gender = "Male" , t.male_std_allele, 0)) AS son_male_std_allele,

		SUM(IF(t.relationship IN ('unrelated') AND t.gender = "Male", t.male_person, 0)) AS unrelated,
		SUM(IF(t.relationship IN ('unrelated') AND t.gender = "Male", t.male_total_allele, 0)) AS unrelated_total_allele,
		SUM(IF(t.relationship IN ('unrelated') AND t.gender = "Male", t.male_inv_allele, 0)) AS unrelated_inv_allele,
		SUM(IF(t.relationship IN ('unrelated') AND t.gender = "Male", t.male_del_allele, 0)) AS unrelated_del_allele,
		SUM(IF(t.relationship IN ('unrelated') AND t.gender = "Male", t.male_std_allele, 0)) AS unrelated_std_allele

	FROM(
		SELECT DISTINCTROW deeper.name, deeper.chr, deeper.population, deeper.family, deeper.relationship, deeper.allele_level, deeper.gender
			,SUM(IF(deeper.genotype LIKE 'STD',1,0)) AS male_std_allele
			,SUM(IF(deeper.gender = 'Male', 1,0)) male_person
			,SUM(IF(deeper.genotype LIKE 'INV',1,0)) AS male_inv_allele
			,SUM(IF(deeper.genotype LIKE 'DEL',1,0)) AS male_del_allele
			,SUM(IF(deeper.genotype LIKE '___',1,0)) AS male_total_allele
			
		FROM( SELECT DISTINCTROW inv.name, inv.chr, i.`code`, i.gender ,i.population, i.trio family, i.trio_relationship relationship, i.allele_level, d.genotype
						FROM inversions AS inv JOIN individuals_detection AS d JOIN individuals AS i JOIN population AS p
							ON (inv.id = d.inversions_id AND d.individuals_id = i.id AND i.population = p.name)
						WHERE d.inversions_id = inv_id_val AND d.validation_research_name IS NOT NULL AND d.genotype IS NOT NULL AND d.genotype NOT IN ('NA', 'ND') 
								AND IF(study_val = 'ALL',d.validation_research_name IS NOT NULL, d.validation_research_name = study_val) 
								AND i.gender NOT IN ('unknown') AND i.gender IS NOT NULL
								AND IF(population_val = 'ALL',i.population IS NOT NULL, i.population = population_val) 
								AND IF(population_id_val = 'ALL',i.population_id IS NOT NULL, i.population_id = population_id_val) 
								AND IF(region_val = 'ALL',p.region IS NOT NULL , p.region = region_val)
								AND (d.allele_comment NOT LIKE '%error%' OR d.allele_comment IS NULL)
				) deeper
			GROUP BY deeper.family, deeper.relationship 
			ORDER BY deeper.family
	) AS t
	GROUP BY t.family

) AS sumary 
) AS ending
) AS last;

	RETURN inv_frequecy;

ELSEIF chrm = 'chrX' THEN

SELECT CONCAT_WS(';', last.indiv, last.alle, last.inv_frequecy,
				CASE  
					WHEN last.HW = "can not be compute" THEN "NA"
					WHEN	last.HW <= 0.4549364 THEN CONCAT('<font color="green">chi-square = ',last.HW,', p-value > 0.5</font>') 
					WHEN	last.HW >  0.4549364  AND last.HW <= 0.7083263  THEN CONCAT( '<font color="green">chi-square = ',last.HW, ', p-value > 0.4</font>') 
					WHEN	last.HW >  0.7083263  AND last.HW <= 1.0741942  THEN CONCAT( '<font color="green">chi-square = ',last.HW, ', p-value > 0.3</font>') 
					WHEN	last.HW >  1.0741942  AND last.HW <= 1.6423744  THEN CONCAT( '<font color="green">chi-square = ',last.HW, ', p-value > 0.2</font>') 
					WHEN	last.HW >  1.6423744  AND last.HW <= 2.7055435  THEN CONCAT( '<font color="green">chi-square = ',last.HW, ', p-value > 0.1</font>')
					WHEN	last.HW >  2.7055435  AND last.HW <= 3.8414588  THEN CONCAT( '<font color="green">chi-square = ',last.HW, ', p-value > 0.05</font>') 
					WHEN	last.HW >  3.8414588  AND last.HW <= 6.6348966  THEN CONCAT( '<font color="red">chi-square = ',last.HW, ', p-value < 0.05</font>') 
					WHEN	last.HW >  6.6348966  AND last.HW <= 7.8794386  THEN CONCAT( '<font color="red">chi-square = ',last.HW, ', p-value < 0.01</font>') 
					WHEN	last.HW >  7.8794386  AND last.HW <= 10.8275662 THEN CONCAT( '<font color="red">chi-square = ',last.HW, ', p-value < 0.005</font>') 
					WHEN	last.HW >  10.8275662 AND last.HW <= 12.1156651 THEN CONCAT( '<font color="red">chi-square = ',last.HW, ', p-value < 0.001</font>') 
					WHEN	last.HW >  12.1156651 AND last.HW <= 15.1367052 THEN CONCAT( '<font color="red">chi-square = ',last.HW, ', p-value < 0.0005</font>') 
					WHEN	last.HW >  15.1367052 THEN CONCAT( '<font color="red">chi-square = ',last.HW,', p-value < 0.0001</font>') 
				END,
				last.std_alle
			)
INTO inv_frequecy
FROM(
SELECT CAST(ending.indiv + ending.male_indiv AS CHAR) AS indiv , CAST(ending.alle_inv + ending.male_alle_inv AS CHAR) AS alle, CAST(ending.alle_std + ending.male_alle_std AS CHAR) AS std_alle,
				CAST(((ending.alle_inv + ending.male_alle_inv)/(ending.total_allel + ending.male_total_allel)) AS CHAR) AS inv_frequecy,
				
				IF((ending.alle_inv = 0 OR ending.alle_inv = ending.total_allel ),"can not be compute",
					IF(ending.male_indiv !=0,
						IF ( ending.alle_del != 0 ,
							TRUNCATE((
											(POW((ending.homo_inv - (POW(ending.alle_inv/ending.total_allel,2)*ending.indiv)),2)/(POW(ending.alle_inv/ending.total_allel,2)*ending.indiv))
											+
											(POW((ending.homo_del - (POW(ending.alle_del/ending.total_allel,2)*ending.indiv)),2)/(POW(ending.alle_del/ending.total_allel,2)*ending.indiv))
											+
											(POW((ending.homo_std - (POW(ending.alle_std/ending.total_allel,2)*ending.indiv)),2)/(POW(ending.alle_std/ending.total_allel,2)*ending.indiv))
											+
											(POW((ending.std_inv - (2*(ending.alle_inv/ending.total_allel)*(ending.alle_std/ending.total_allel))*ending.indiv),2)/(2*(ending.alle_inv/ending.total_allel)*(ending.alle_std/ending.total_allel)*ending.indiv))
											+
											(POW((ending.std_del - (2*(ending.alle_del/ending.total_allel)*(ending.alle_std/ending.total_allel))*ending.indiv),2)/(2*(ending.alle_del/ending.total_allel)*(ending.alle_std/ending.total_allel)*ending.indiv))
											+
											(POW((ending.inv_del - (2*(ending.alle_del/ending.total_allel)*(ending.alle_inv/ending.total_allel))*ending.indiv),2)/(2*(ending.alle_del/ending.total_allel)*(ending.alle_inv/ending.total_allel)*ending.indiv))
											+
											(POW((ending.male_alle_inv - (((ending.alle_inv + ending.male_alle_inv)/(ending.total_allel + ending.male_total_allel))*ending.male_indiv)),2)/(((ending.alle_inv + ending.male_alle_inv)/(ending.total_allel + ending.male_total_allel))*ending.male_indiv))
											+
											(POW((ending.male_alle_std - (((ending.alle_std + ending.male_alle_std)/(ending.total_allel + ending.male_total_allel))*ending.male_indiv)),2)/(((ending.alle_std + ending.male_alle_std)/(ending.total_allel + ending.male_total_allel))*ending.male_indiv))
											+
											(POW((ending.male_alle_del - (((ending.alle_del + ending.male_alle_del)/(ending.total_allel + ending.male_total_allel))*ending.male_indiv)),2)/(((ending.alle_del + ending.male_alle_del)/(ending.total_allel + ending.male_total_allel))*ending.male_indiv))
											),4)
						,
							TRUNCATE((
											(POW((ending.homo_inv - (POW(ending.alle_inv/ending.total_allel,2)*ending.indiv)),2)/(POW(ending.alle_inv/ending.total_allel,2)*ending.indiv))
											+
											(POW((ending.homo_std - (POW(ending.alle_std/ending.total_allel,2)*ending.indiv)),2)/(POW(ending.alle_std/ending.total_allel,2)*ending.indiv))
											+
											(POW((ending.std_inv - (2*(ending.alle_inv/ending.total_allel)*(ending.alle_std/ending.total_allel))*ending.indiv),2)/(2*(ending.alle_inv/ending.total_allel)*(ending.alle_std/ending.total_allel)*ending.indiv))
											+
											(POW((ending.male_alle_inv - (((ending.alle_inv + ending.male_alle_inv)/(ending.total_allel + ending.male_total_allel))*ending.male_indiv)),2)/(((ending.alle_inv + ending.male_alle_inv)/(ending.total_allel + ending.male_total_allel))*ending.male_indiv))
											+
											(POW((ending.male_alle_std - (((ending.alle_std + ending.male_alle_std)/(ending.total_allel + ending.male_total_allel))*ending.male_indiv)),2)/(((ending.alle_std + ending.male_alle_std)/(ending.total_allel + ending.male_total_allel))*ending.male_indiv))
											),4)
							)
						,
						IF ( ending.alle_del != 0 ,
							TRUNCATE((
											(POW((ending.homo_inv - (POW(ending.alle_inv/ending.total_allel,2)*ending.indiv)),2)/(POW(ending.alle_inv/ending.total_allel,2)*ending.indiv))
											+
											(POW((ending.homo_del - (POW(ending.alle_del/ending.total_allel,2)*ending.indiv)),2)/(POW(ending.alle_del/ending.total_allel,2)*ending.indiv))
											+
											(POW((ending.homo_std - (POW(ending.alle_std/ending.total_allel,2)*ending.indiv)),2)/(POW(ending.alle_std/ending.total_allel,2)*ending.indiv))
											+
											(POW((ending.std_inv - (2*(ending.alle_inv/ending.total_allel)*(ending.alle_std/ending.total_allel))*ending.indiv),2)/(2*(ending.alle_inv/ending.total_allel)*(ending.alle_std/ending.total_allel)*ending.indiv))
											+
											(POW((ending.std_del - (2*(ending.alle_del/ending.total_allel)*(ending.alle_std/ending.total_allel))*ending.indiv),2)/(2*(ending.alle_del/ending.total_allel)*(ending.alle_std/ending.total_allel)*ending.indiv))
											+
											(POW((ending.inv_del - (2*(ending.alle_del/ending.total_allel)*(ending.alle_inv/ending.total_allel))*ending.indiv),2)/(2*(ending.alle_del/ending.total_allel)*(ending.alle_inv/ending.total_allel)*ending.indiv))
											),4)
						,
							TRUNCATE((
											(POW((ending.homo_inv - (POW(ending.alle_inv/ending.total_allel,2)*ending.indiv)),2)/(POW(ending.alle_inv/ending.total_allel,2)*ending.indiv))
											+
											(POW((ending.homo_std - (POW(ending.alle_std/ending.total_allel,2)*ending.indiv)),2)/(POW(ending.alle_std/ending.total_allel,2)*ending.indiv))
											+
											(POW((ending.std_inv - (2*(ending.alle_inv/ending.total_allel)*(ending.alle_std/ending.total_allel))*ending.indiv),2)/(2*(ending.alle_inv/ending.total_allel)*(ending.alle_std/ending.total_allel)*ending.indiv))
											),4)
							)
					)
				 ) AS HW

FROM(

SELECT SUM( IF(sumary.mat_ascendent = 0 AND sumary.pat_ascendent = 0,
											sumary.son_female + sumary.unrelated,
											IF(sumary.mat_ascendent != 0 OR sumary.pat_ascendent != 0,
																sumary.mat_ascendent + sumary.unrelated,0))
					) AS indiv,

				SUM( IF(sumary.mat_ascendent = 0 AND sumary.pat_ascendent = 0,
											sumary.son_male + sumary.unrelated_male,
											IF(sumary.mat_ascendent != 0 OR sumary.pat_ascendent != 0,
																sumary.pat_ascendent + sumary.unrelated_male,0))
					) AS male_indiv,

				SUM( IF(sumary.mat_ascendent = 0 AND sumary.pat_ascendent = 0,
											sumary.son_female_total_allele + sumary.unrelated_total_allele,
											IF(sumary.mat_ascendent != 0 OR sumary.pat_ascendent != 0,
																sumary.mat_total_allele + sumary.unrelated_total_allele, 0))
					) AS total_allel,

				SUM( IF(sumary.mat_ascendent = 0 AND sumary.pat_ascendent = 0,
											sumary.son_male_total_allele + sumary.unrelated_male_total_allele,
											IF(sumary.mat_ascendent != 0 OR sumary.pat_ascendent != 0,
																sumary.pat_total_allele + sumary.unrelated_male_total_allele, 0))
					) AS male_total_allel,

			 SUM( IF(sumary.mat_ascendent = 0 AND sumary.pat_ascendent = 0,
											sumary.son_female_inv_allele + sumary.unrelated_inv_allele,
											IF(sumary.mat_ascendent != 0 OR sumary.pat_ascendent != 0,
																sumary.mat_inv_allele + sumary.unrelated_inv_allele, 0))
					) AS alle_inv,

			 SUM( IF(sumary.mat_ascendent = 0 AND sumary.pat_ascendent = 0,
											sumary.son_male_inv_allele + sumary.unrelated_male_inv_allele,
											IF(sumary.mat_ascendent != 0 OR sumary.pat_ascendent != 0,
																sumary.pat_inv_allele + sumary.unrelated_male_inv_allele, 0))
					) AS male_alle_inv,
		
			 SUM( IF(sumary.mat_ascendent = 0 AND sumary.pat_ascendent = 0,
											sumary.son_female_del_allele + sumary.unrelated_del_allele,
											IF(sumary.mat_ascendent != 0 OR sumary.pat_ascendent != 0,
																sumary.mat_del_allele + sumary.unrelated_del_allele, 0))
					) AS alle_del,

			 SUM( IF(sumary.mat_ascendent = 0 AND sumary.pat_ascendent = 0,
											sumary.son_male_del_allele + sumary.unrelated_male_del_allele,
											IF(sumary.mat_ascendent != 0 OR sumary.pat_ascendent != 0,
																sumary.pat_del_allele + sumary.unrelated_male_del_allele, 0))
					) AS male_alle_del,

			 SUM( IF(sumary.mat_ascendent = 0 AND sumary.pat_ascendent = 0,
											sumary.son_female_std_allele + sumary.unrelated_std_allele,
											IF(sumary.mat_ascendent != 0 OR sumary.pat_ascendent != 0,
																sumary.mat_std_allele + sumary.unrelated_std_allele, 0))
					) AS alle_std,

			 SUM( IF(sumary.mat_ascendent = 0 AND sumary.pat_ascendent = 0,
											sumary.son_male_std_allele + sumary.unrelated_male_std_allele,
											IF(sumary.mat_ascendent != 0 OR sumary.pat_ascendent != 0,
																sumary.pat_std_allele + sumary.unrelated_male_std_allele, 0))
					) AS male_alle_std,
				
				SUM( IF(sumary.mat_ascendent = 0 AND sumary.pat_ascendent = 0,
											sumary.son_female_homo_inv + sumary.unrelated_homo_inv,
											IF(sumary.mat_ascendent != 0 OR sumary.pat_ascendent != 0,
																sumary.mat_homo_inv + sumary.unrelated_homo_inv, 0))
						) AS homo_inv,

				SUM( IF(sumary.mat_ascendent = 0 AND sumary.pat_ascendent = 0,
											sumary.son_female_homo_std + sumary.unrelated_homo_std,
											IF(sumary.mat_ascendent != 0 OR sumary.pat_ascendent != 0,
																sumary.mat_homo_std + sumary.unrelated_homo_std, 0))
						) AS homo_std,

				SUM( IF(sumary.mat_ascendent = 0 AND sumary.pat_ascendent = 0,
											sumary.son_female_homo_del + sumary.unrelated_homo_del,
											IF(sumary.mat_ascendent != 0 OR sumary.pat_ascendent != 0,
																sumary.mat_homo_del + sumary.unrelated_homo_del, 0))
						) AS homo_del,

				SUM( IF(sumary.mat_ascendent = 0 AND sumary.pat_ascendent = 0,
											sumary.son_female_std_inv + sumary.unrelated_std_inv,
											IF(sumary.mat_ascendent != 0 OR sumary.pat_ascendent != 0,
																sumary.mat_std_inv + sumary.unrelated_std_inv, 0))
						) AS std_inv,

				SUM( IF(sumary.mat_ascendent = 0 AND sumary.pat_ascendent = 0,
											sumary.son_female_std_del + sumary.unrelated_std_del,
											IF(sumary.mat_ascendent != 0 OR sumary.pat_ascendent != 0,
																sumary.mat_std_del + sumary.unrelated_std_del, 0)) 
						) AS std_del,

				SUM( IF(sumary.mat_ascendent = 0 AND sumary.pat_ascendent = 0,
											sumary.son_female_inv_del + sumary.unrelated_inv_del,
											IF(sumary.mat_ascendent != 0 OR sumary.pat_ascendent != 0,
																sumary.mat_inv_del + sumary.unrelated_inv_del, 0))
						) AS inv_del
				
FROM(
	SELECT t.name,t.chr, t.population, t.family, t.relationship, 
		SUM(IF(t.relationship IN ('mother'), t.female_person, 0)) AS mat_ascendent,
		SUM(IF(t.relationship IN ('mother'), t.total_allele, 0)) AS mat_total_allele,
		SUM(IF(t.relationship IN ('mother'), t.inv_allele, 0)) AS mat_inv_allele,
		SUM(IF(t.relationship IN ('mother'), t.del_allele, 0)) AS mat_del_allele,
		SUM(IF(t.relationship IN ('mother'), t.std_allele, 0)) AS mat_std_allele,
		SUM(IF(t.relationship IN ('mother'), t.homo_inv, 0)) AS mat_homo_inv,
		SUM(IF(t.relationship IN ('mother'), t.homo_std, 0)) AS mat_homo_std,
		SUM(IF(t.relationship IN ('mother'), t.homo_del, 0)) AS mat_homo_del,
		SUM(IF(t.relationship IN ('mother'), t.std_inv, 0)) AS mat_std_inv,
		SUM(IF(t.relationship IN ('mother'), t.inv_del, 0)) AS mat_inv_del,
		SUM(IF(t.relationship IN ('mother'), t.std_del, 0)) AS mat_std_del,
		
		SUM(IF(t.relationship IN ('father'), t.male_person, 0)) AS pat_ascendent,
		SUM(IF(t.relationship IN ('father'), t.male_total_allele, 0)) AS pat_total_allele,
		SUM(IF(t.relationship IN ('father'), t.male_inv_allele, 0)) AS pat_inv_allele,
		SUM(IF(t.relationship IN ('father'), t.male_del_allele, 0)) AS pat_del_allele,
		SUM(IF(t.relationship IN ('father'), t.male_std_allele, 0)) AS pat_std_allele,

		SUM(IF(t.relationship IN ('child') AND t.gender = "Male" , t.male_person, 0)) AS son_male,
		SUM(IF(t.relationship IN ('child') AND t.gender = "Male" , t.male_total_allele, 0)) AS son_male_total_allele,
		SUM(IF(t.relationship IN ('child') AND t.gender = "Male" , t.male_inv_allele, 0)) AS son_male_inv_allele,
		SUM(IF(t.relationship IN ('child') AND t.gender = "Male" , t.male_del_allele, 0)) AS son_male_del_allele,
		SUM(IF(t.relationship IN ('child') AND t.gender = "Male" , t.male_std_allele, 0)) AS son_male_std_allele,

		SUM(IF(t.relationship IN ('child') AND t.gender = "Female" , t.female_person, 0)) AS son_female,
		SUM(IF(t.relationship IN ('child') AND t.gender = "Female" , t.total_allele, 0)) AS son_female_total_allele,
		SUM(IF(t.relationship IN ('child') AND t.gender = "Female" , t.inv_allele, 0)) AS son_female_inv_allele,
		SUM(IF(t.relationship IN ('child') AND t.gender = "Female" , t.del_allele, 0)) AS son_female_del_allele,
		SUM(IF(t.relationship IN ('child') AND t.gender = "Female" , t.std_allele, 0)) AS son_female_std_allele,
		SUM(IF(t.relationship IN ('child') AND t.gender = "Female" , t.homo_inv, 0)) AS son_female_homo_inv,
		SUM(IF(t.relationship IN ('child') AND t.gender = "Female" , t.homo_std, 0)) AS son_female_homo_std,
		SUM(IF(t.relationship IN ('child') AND t.gender = "Female" , t.homo_del, 0)) AS son_female_homo_del,
		SUM(IF(t.relationship IN ('child') AND t.gender = "Female" , t.std_inv, 0)) AS son_female_std_inv,		
		SUM(IF(t.relationship IN ('child') AND t.gender = "Female" , t.inv_del, 0)) AS son_female_inv_del,
		SUM(IF(t.relationship IN ('child') AND t.gender = "Female" , t.std_del, 0)) AS son_female_std_del,

		SUM(IF(t.relationship IN ('unrelated') , t.female_person, 0)) AS unrelated,
		SUM(IF(t.relationship IN ('unrelated') , t.total_allele, 0)) AS unrelated_total_allele,
		SUM(IF(t.relationship IN ('unrelated') , t.inv_allele, 0)) AS unrelated_inv_allele,
		SUM(IF(t.relationship IN ('unrelated') , t.del_allele, 0)) AS unrelated_del_allele,
		SUM(IF(t.relationship IN ('unrelated') , t.std_allele, 0)) AS unrelated_std_allele,
		SUM(IF(t.relationship IN ('unrelated') , t.homo_inv, 0)) AS unrelated_homo_inv,
		SUM(IF(t.relationship IN ('unrelated') , t.homo_std, 0)) AS unrelated_homo_std,
		SUM(IF(t.relationship IN ('unrelated') , t.homo_del, 0)) AS unrelated_homo_del,
		SUM(IF(t.relationship IN ('unrelated') , t.std_inv, 0)) AS unrelated_std_inv,
		SUM(IF(t.relationship IN ('unrelated') , t.inv_del, 0)) AS unrelated_inv_del,
		SUM(IF(t.relationship IN ('unrelated') , t.std_del, 0)) AS unrelated_std_del,

		SUM(IF(t.relationship IN ('unrelated') , t.male_person, 0)) AS unrelated_male,
		SUM(IF(t.relationship IN ('unrelated') , t.male_total_allele, 0)) AS unrelated_male_total_allele,
		SUM(IF(t.relationship IN ('unrelated') , t.male_inv_allele, 0)) AS unrelated_male_inv_allele,
		SUM(IF(t.relationship IN ('unrelated') , t.male_del_allele, 0)) AS unrelated_male_del_allele,
		SUM(IF(t.relationship IN ('unrelated') , t.male_std_allele, 0)) AS unrelated_male_std_allele



	FROM(
		SELECT DISTINCTROW deeper.name, deeper.chr, deeper.population, deeper.family, deeper.relationship, deeper.allele_level, deeper.gender
			,SUM(IF(deeper.genotype LIKE 'INV_INV',2,IF(deeper.genotype LIKE 'STD_INV',1,IF(deeper.genotype LIKE 'INV_DEL',1,0)))) AS inv_allele
			,SUM(IF(deeper.genotype LIKE 'DEL_DEL',2,IF(deeper.genotype LIKE 'STD_DEL',1,IF(deeper.genotype LIKE 'INV_DEL',1,0)))) AS del_allele
			,SUM(IF(deeper.genotype LIKE 'STD_STD',2,IF(deeper.genotype LIKE 'STD_INV',1,IF(deeper.genotype LIKE 'STD_DEL',1,0)))) AS std_allele
			,SUM(IF(deeper.genotype LIKE '_______',2,0)) AS total_allele
			,SUM(IF(deeper.genotype LIKE 'INV_INV',1,0)) AS homo_inv
			,SUM(IF(deeper.genotype LIKE 'STD_STD',1,0)) AS homo_std
			,SUM(IF(deeper.genotype LIKE 'DEL_DEL',1,0)) AS homo_del
			,SUM(IF(deeper.genotype LIKE 'STD_INV',1,0)) AS std_inv
			,SUM(IF(deeper.genotype LIKE 'STD_DEL',1,0)) AS std_del
			,SUM(IF(deeper.genotype LIKE 'INV_DEL',1,0)) AS inv_del
			,SUM(IF(deeper.gender = 'Female', 1,0)) female_person
			
			,SUM(IF(deeper.genotype LIKE 'STD',1,0)) AS male_std_allele
			,SUM(IF(deeper.gender = 'Male', 1,0)) male_person
			,SUM(IF(deeper.genotype LIKE 'INV',1,0)) AS male_inv_allele
			,SUM(IF(deeper.genotype LIKE 'DEL',1,0)) AS male_del_allele
			,SUM(IF(deeper.genotype LIKE '___',1,0)) AS male_total_allele
			
		FROM( SELECT DISTINCTROW inv.name, inv.chr, i.`code`, i.gender ,i.population, i.trio family, i.trio_relationship relationship, i.allele_level, d.genotype
						FROM inversions AS inv JOIN individuals_detection AS d JOIN individuals AS i JOIN population AS p
							ON (inv.id = d.inversions_id AND d.individuals_id = i.id AND i.population = p.name)
						WHERE d.inversions_id = inv_id_val AND d.validation_research_name IS NOT NULL AND d.genotype IS NOT NULL AND d.genotype NOT IN ('NA', 'ND') 
								AND i.gender NOT IN ('unknown') AND i.gender IS NOT NULL
								AND IF(study_val = 'ALL',d.validation_research_name IS NOT NULL, d.validation_research_name = study_val) 
								AND IF(population_val = 'ALL',i.population IS NOT NULL, i.population = population_val) 
								AND IF(population_id_val = 'ALL',i.population_id IS NOT NULL, i.population_id = population_id_val) 
								AND IF(region_val = 'ALL',p.region IS NOT NULL, p.region = region_val)
								AND (d.allele_comment NOT LIKE '%error%' OR d.allele_comment IS NULL)
				) deeper
			GROUP BY deeper.family, deeper.relationship 
			ORDER BY deeper.family
	) AS t
	GROUP BY t.family

) AS sumary 
) AS ending
) AS last;

	RETURN inv_frequecy;


ELSE

SELECT CONCAT_WS(';', last.indiv, last.alle, last.inv_frequecy,
				CASE  
					WHEN last.HW = "can not be compute" THEN "NA"
					WHEN	last.HW <= 0.4549364 THEN CONCAT('<font color="green">chi-square = ',last.HW,', p-value > 0.5</font>') 
					WHEN	last.HW >  0.4549364  AND last.HW <= 0.7083263  THEN CONCAT( '<font color="green">chi-square = ',last.HW, ', p-value > 0.4</font>') 
					WHEN	last.HW >  0.7083263  AND last.HW <= 1.0741942  THEN CONCAT( '<font color="green">chi-square = ',last.HW, ', p-value > 0.3</font>') 
					WHEN	last.HW >  1.0741942  AND last.HW <= 1.6423744  THEN CONCAT( '<font color="green">chi-square = ',last.HW, ', p-value > 0.2</font>') 
					WHEN	last.HW >  1.6423744  AND last.HW <= 2.7055435  THEN CONCAT( '<font color="green">chi-square = ',last.HW, ', p-value > 0.1</font>')
					WHEN	last.HW >  2.7055435  AND last.HW <= 3.8414588  THEN CONCAT( '<font color="green">chi-square = ',last.HW, ', p-value > 0.05</font>') 
					WHEN	last.HW >  3.8414588  AND last.HW <= 6.6348966  THEN CONCAT( '<font color="red">chi-square = ',last.HW, ', p-value < 0.05</font>') 
					WHEN	last.HW >  6.6348966  AND last.HW <= 7.8794386  THEN CONCAT( '<font color="red">chi-square = ',last.HW, ', p-value < 0.01</font>') 
					WHEN	last.HW >  7.8794386  AND last.HW <= 10.8275662 THEN CONCAT( '<font color="red">chi-square = ',last.HW, ', p-value < 0.005</font>') 
					WHEN	last.HW >  10.8275662 AND last.HW <= 12.1156651 THEN CONCAT( '<font color="red">chi-square = ',last.HW, ', p-value < 0.001</font>') 
					WHEN	last.HW >  12.1156651 AND last.HW <= 15.1367052 THEN CONCAT( '<font color="red">chi-square = ',last.HW, ', p-value < 0.0005</font>') 
					WHEN	last.HW >  15.1367052 THEN CONCAT( '<font color="red">chi-square = ',last.HW,', p-value < 0.0001</font>') 
				END,
				last.std_alle
			)
INTO inv_frequecy
FROM(
SELECT CAST(ending.indiv AS CHAR) AS indiv , CAST(ending.alle_inv AS CHAR) AS alle,  CAST(ending.alle_std AS CHAR) AS std_alle,
				CAST(ending.alle_inv/ending.total_allel AS CHAR) AS inv_frequecy, 
				IF((ending.alle_inv = 0 OR ending.alle_inv = ending.total_allel ),"can not be compute",
						IF ( ending.alle_del != 0 ,
							TRUNCATE((
											(POW((ending.homo_inv - (POW(ending.alle_inv/ending.total_allel,2)*ending.indiv)),2)/(POW(ending.alle_inv/ending.total_allel,2)*ending.indiv))
											+
											(POW((ending.homo_del - (POW(ending.alle_del/ending.total_allel,2)*ending.indiv)),2)/(POW(ending.alle_del/ending.total_allel,2)*ending.indiv))
											+
											(POW((ending.homo_std - (POW(ending.alle_std/ending.total_allel,2)*ending.indiv)),2)/(POW(ending.alle_std/ending.total_allel,2)*ending.indiv))
											+
											(POW((ending.std_inv - (2*(ending.alle_inv/ending.total_allel)*(ending.alle_std/ending.total_allel))*ending.indiv),2)/(2*(ending.alle_inv/ending.total_allel)*(ending.alle_std/ending.total_allel)*ending.indiv))
											+
											(POW((ending.std_del - (2*(ending.alle_del/ending.total_allel)*(ending.alle_std/ending.total_allel))*ending.indiv),2)/(2*(ending.alle_del/ending.total_allel)*(ending.alle_std/ending.total_allel)*ending.indiv))
											+
											(POW((ending.inv_del - (2*(ending.alle_del/ending.total_allel)*(ending.alle_inv/ending.total_allel))*ending.indiv),2)/(2*(ending.alle_del/ending.total_allel)*(ending.alle_inv/ending.total_allel)*ending.indiv))
											),4)
						,
							TRUNCATE((
											(POW((ending.homo_inv - (POW(ending.alle_inv/ending.total_allel,2)*ending.indiv)),2)/(POW(ending.alle_inv/ending.total_allel,2)*ending.indiv))
											+
											(POW((ending.homo_std - (POW(ending.alle_std/ending.total_allel,2)*ending.indiv)),2)/(POW(ending.alle_std/ending.total_allel,2)*ending.indiv))
											+
											(POW((ending.std_inv - (2*(ending.alle_inv/ending.total_allel)*(ending.alle_std/ending.total_allel))*ending.indiv),2)/(2*(ending.alle_inv/ending.total_allel)*(ending.alle_std/ending.total_allel)*ending.indiv))
											),4)
							)
					)
				AS HW

FROM(

SELECT SUM( IF(sumary.mat_ascendent = 0 AND sumary.pat_ascendent = 0,
											sumary.son_female + sumary.son_male + sumary.unrelated,
											IF(sumary.mat_ascendent != 0 OR sumary.pat_ascendent != 0,
																sumary.mat_ascendent + sumary.pat_ascendent + sumary.unrelated,0))
					) AS indiv,
				
				SUM( IF(sumary.mat_ascendent = 0 AND sumary.pat_ascendent = 0,
											sumary.son_female_total_allele + sumary.son_male_total_allele + sumary.unrelated_total_allele,
											IF(sumary.mat_ascendent != 0 OR sumary.pat_ascendent != 0,
																sumary.mat_total_allele + sumary.pat_total_allele + sumary.unrelated_total_allele, 0))
					) AS total_allel,

			 SUM( IF(sumary.mat_ascendent = 0 AND sumary.pat_ascendent = 0,
											sumary.son_female_inv_allele + sumary.son_male_inv_allele + sumary.unrelated_inv_allele,
											IF(sumary.mat_ascendent != 0 OR sumary.pat_ascendent != 0,
																sumary.mat_inv_allele + sumary.pat_inv_allele + sumary.unrelated_inv_allele, 0))
					) AS alle_inv,
					
			 SUM( IF(sumary.mat_ascendent = 0 AND sumary.pat_ascendent = 0,
											sumary.son_female_del_allele + sumary.son_male_del_allele + sumary.unrelated_del_allele,
											IF(sumary.mat_ascendent != 0 OR sumary.pat_ascendent != 0,
																sumary.mat_del_allele + sumary.pat_del_allele + sumary.unrelated_del_allele, 0))
					) AS alle_del,

			 SUM( IF(sumary.mat_ascendent = 0 AND sumary.pat_ascendent = 0,
											sumary.son_female_std_allele + sumary.son_male_std_allele + sumary.unrelated_std_allele,
											IF(sumary.mat_ascendent != 0 OR sumary.pat_ascendent != 0,
																sumary.mat_std_allele + sumary.pat_std_allele + sumary.unrelated_std_allele, 0))
					) AS alle_std,
				
				SUM( IF(sumary.mat_ascendent = 0 AND sumary.pat_ascendent = 0,
											sumary.son_female_homo_inv + sumary.son_male_homo_inv + sumary.unrelated_homo_inv,
											IF(sumary.mat_ascendent != 0 OR sumary.pat_ascendent != 0,
																sumary.mat_homo_inv + sumary.pat_homo_inv + sumary.unrelated_homo_inv, 0))
						) AS homo_inv,

				SUM( IF(sumary.mat_ascendent = 0 AND sumary.pat_ascendent = 0,
											sumary.son_female_homo_std + sumary.son_male_homo_std + sumary.unrelated_homo_std,
											IF(sumary.mat_ascendent != 0 OR sumary.pat_ascendent != 0,
																sumary.mat_homo_std + sumary.pat_homo_std + sumary.unrelated_homo_std, 0))
						) AS homo_std,

				SUM( IF(sumary.mat_ascendent = 0 AND sumary.pat_ascendent = 0,
											sumary.son_female_homo_del + sumary.son_male_homo_del + sumary.unrelated_homo_del,
											IF(sumary.mat_ascendent != 0 OR sumary.pat_ascendent != 0,
																sumary.mat_homo_del + sumary.pat_homo_del + sumary.unrelated_homo_del, 0))
						) AS homo_del,

				SUM( IF(sumary.mat_ascendent = 0 AND sumary.pat_ascendent = 0,
											sumary.son_female_std_inv + sumary.son_male_std_inv + sumary.unrelated_std_inv,
											IF(sumary.mat_ascendent != 0 OR sumary.pat_ascendent != 0,
																sumary.mat_std_inv + sumary.pat_std_inv + sumary.unrelated_std_inv, 0))
						) AS std_inv,

				SUM( IF(sumary.mat_ascendent = 0 AND sumary.pat_ascendent = 0,
											sumary.son_female_std_del + sumary.son_male_std_del + sumary.unrelated_std_del,
											IF(sumary.mat_ascendent != 0 OR sumary.pat_ascendent != 0,
																sumary.mat_std_del + sumary.pat_std_del + sumary.unrelated_std_del, 0)) 
						) AS std_del,

				SUM( IF(sumary.mat_ascendent = 0 AND sumary.pat_ascendent = 0,
											sumary.son_female_inv_del + sumary.son_male_inv_del + sumary.unrelated_inv_del,
											IF(sumary.mat_ascendent != 0 OR sumary.pat_ascendent != 0,
																sumary.mat_inv_del + sumary.pat_inv_del + sumary.unrelated_inv_del, 0))
						) AS inv_del
				
FROM(
	SELECT t.name,t.chr, t.population, t.family, t.relationship, 
		SUM(IF(t.relationship IN ('mother'), t.female_person, 0)) AS mat_ascendent,
		SUM(IF(t.relationship IN ('mother'), t.total_allele, 0)) AS mat_total_allele,
		SUM(IF(t.relationship IN ('mother'), t.inv_allele, 0)) AS mat_inv_allele,
		SUM(IF(t.relationship IN ('mother'), t.del_allele, 0)) AS mat_del_allele,
		SUM(IF(t.relationship IN ('mother'), t.std_allele, 0)) AS mat_std_allele,
		SUM(IF(t.relationship IN ('mother'), t.homo_inv, 0)) AS mat_homo_inv,
		SUM(IF(t.relationship IN ('mother'), t.homo_std, 0)) AS mat_homo_std,
		SUM(IF(t.relationship IN ('mother'), t.homo_del, 0)) AS mat_homo_del,
		SUM(IF(t.relationship IN ('mother'), t.std_inv, 0)) AS mat_std_inv,
		SUM(IF(t.relationship IN ('mother'), t.inv_del, 0)) AS mat_inv_del,
		SUM(IF(t.relationship IN ('mother'), t.std_del, 0)) AS mat_std_del,
		
		SUM(IF(t.relationship IN ('father'), t.male_person, 0)) AS pat_ascendent,
		SUM(IF(t.relationship IN ('father'), t.total_allele, 0)) AS pat_total_allele,
		SUM(IF(t.relationship IN ('father'), t.inv_allele, 0)) AS pat_inv_allele,
		SUM(IF(t.relationship IN ('father'), t.del_allele, 0)) AS pat_del_allele,
		SUM(IF(t.relationship IN ('father'), t.std_allele, 0)) AS pat_std_allele,
		SUM(IF(t.relationship IN ('father'), t.homo_inv, 0)) AS pat_homo_inv,
		SUM(IF(t.relationship IN ('father'), t.homo_std, 0)) AS pat_homo_std,
		SUM(IF(t.relationship IN ('father'), t.homo_del, 0)) AS pat_homo_del,
		SUM(IF(t.relationship IN ('father'), t.std_inv, 0)) AS pat_std_inv,
		SUM(IF(t.relationship IN ('father'), t.inv_del, 0)) AS pat_inv_del,
		SUM(IF(t.relationship IN ('father'), t.std_del, 0)) AS pat_std_del,

		SUM(IF(t.relationship IN ('child') AND t.gender = "Male" , t.male_person, 0)) AS son_male,
		SUM(IF(t.relationship IN ('child') AND t.gender = "Male" , t.total_allele, 0)) AS son_male_total_allele,
		SUM(IF(t.relationship IN ('child') AND t.gender = "Male" , t.inv_allele, 0)) AS son_male_inv_allele,
		SUM(IF(t.relationship IN ('child') AND t.gender = "Male" , t.del_allele, 0)) AS son_male_del_allele,
		SUM(IF(t.relationship IN ('child') AND t.gender = "Male" , t.std_allele, 0)) AS son_male_std_allele,
		SUM(IF(t.relationship IN ('child') AND t.gender = "Male" , t.homo_inv, 0)) AS son_male_homo_inv,
		SUM(IF(t.relationship IN ('child') AND t.gender = "Male" , t.homo_std, 0)) AS son_male_homo_std,
		SUM(IF(t.relationship IN ('child') AND t.gender = "Male" , t.homo_del, 0)) AS son_male_homo_del,
		SUM(IF(t.relationship IN ('child') AND t.gender = "Male" , t.std_inv, 0)) AS son_male_std_inv,
		SUM(IF(t.relationship IN ('child') AND t.gender = "Male" , t.inv_del, 0)) AS son_male_inv_del,
		SUM(IF(t.relationship IN ('child') AND t.gender = "Male" , t.std_del, 0)) AS son_male_std_del,

		SUM(IF(t.relationship IN ('child') AND t.gender = "Female" , t.female_person, 0)) AS son_female,
		SUM(IF(t.relationship IN ('child') AND t.gender = "Female" , t.total_allele, 0)) AS son_female_total_allele,
		SUM(IF(t.relationship IN ('child') AND t.gender = "Female" , t.inv_allele, 0)) AS son_female_inv_allele,
		SUM(IF(t.relationship IN ('child') AND t.gender = "Female" , t.del_allele, 0)) AS son_female_del_allele,
		SUM(IF(t.relationship IN ('child') AND t.gender = "Female" , t.std_allele, 0)) AS son_female_std_allele,
		SUM(IF(t.relationship IN ('child') AND t.gender = "Female" , t.homo_inv, 0)) AS son_female_homo_inv,
		SUM(IF(t.relationship IN ('child') AND t.gender = "Female" , t.homo_std, 0)) AS son_female_homo_std,
		SUM(IF(t.relationship IN ('child') AND t.gender = "Female" , t.homo_del, 0)) AS son_female_homo_del,
		SUM(IF(t.relationship IN ('child') AND t.gender = "Female" , t.std_inv, 0)) AS son_female_std_inv,		
		SUM(IF(t.relationship IN ('child') AND t.gender = "Female" , t.inv_del, 0)) AS son_female_inv_del,
		SUM(IF(t.relationship IN ('child') AND t.gender = "Female" , t.std_del, 0)) AS son_female_std_del,

		SUM(IF(t.relationship IN ('unrelated') , t.male_person + t.female_person+t.unknown_person, 0)) AS unrelated,
		SUM(IF(t.relationship IN ('unrelated') , t.total_allele, 0)) AS unrelated_total_allele,
		SUM(IF(t.relationship IN ('unrelated') , t.inv_allele, 0)) AS unrelated_inv_allele,
		SUM(IF(t.relationship IN ('unrelated') , t.del_allele, 0)) AS unrelated_del_allele,
		SUM(IF(t.relationship IN ('unrelated') , t.std_allele, 0)) AS unrelated_std_allele,
		SUM(IF(t.relationship IN ('unrelated') , t.homo_inv, 0)) AS unrelated_homo_inv,
		SUM(IF(t.relationship IN ('unrelated') , t.homo_std, 0)) AS unrelated_homo_std,
		SUM(IF(t.relationship IN ('unrelated') , t.homo_del, 0)) AS unrelated_homo_del,
		SUM(IF(t.relationship IN ('unrelated') , t.std_inv, 0)) AS unrelated_std_inv,
		SUM(IF(t.relationship IN ('unrelated') , t.inv_del, 0)) AS unrelated_inv_del,
		SUM(IF(t.relationship IN ('unrelated') , t.std_del, 0)) AS unrelated_std_del

	FROM(
		SELECT DISTINCTROW deeper.name, deeper.chr, deeper.population, deeper.family, deeper.relationship, deeper.allele_level, deeper.gender
			,SUM(IF(deeper.genotype LIKE 'INV_INV',2,IF(deeper.genotype LIKE 'STD_INV',1,IF(deeper.genotype LIKE 'INV_DEL',1,0)))) AS inv_allele
			,SUM(IF(deeper.genotype LIKE 'DEL_DEL',2,IF(deeper.genotype LIKE 'STD_DEL',1,IF(deeper.genotype LIKE 'INV_DEL',1,0)))) AS del_allele
			,SUM(IF(deeper.genotype LIKE 'STD_STD',2,IF(deeper.genotype LIKE 'STD_INV',1,IF(deeper.genotype LIKE 'STD_DEL',1,0)))) AS std_allele
			,SUM(IF(deeper.genotype LIKE '_______',2,0)) AS total_allele
			,SUM(IF(deeper.genotype LIKE 'INV_INV',1,0)) AS homo_inv
			,SUM(IF(deeper.genotype LIKE 'STD_STD',1,0)) AS homo_std
			,SUM(IF(deeper.genotype LIKE 'DEL_DEL',1,0)) AS homo_del
			,SUM(IF(deeper.genotype LIKE 'STD_INV',1,0)) AS std_inv
			,SUM(IF(deeper.genotype LIKE 'STD_DEL',1,0)) AS std_del
			,SUM(IF(deeper.genotype LIKE 'INV_DEL',1,0)) AS inv_del
			,SUM(IF(deeper.gender = 'Female', 1,0)) female_person
			,SUM(IF(deeper.gender = 'Male', 1,0)) male_person
			,SUM(IF(deeper.gender = 'unknown', 1,0)) unknown_person
			
		FROM( SELECT DISTINCTROW inv.name, inv.chr, i.`code`, i.gender ,i.population, i.trio family, i.trio_relationship relationship, i.allele_level, d.genotype
						FROM inversions AS inv JOIN individuals_detection AS d JOIN individuals AS i JOIN population AS p
							ON (inv.id = d.inversions_id AND d.individuals_id = i.id AND i.population = p.name)
						WHERE d.inversions_id = inv_id_val AND d.validation_research_name IS NOT NULL AND d.genotype IS NOT NULL AND d.genotype NOT IN ('NA', 'ND') 
								AND IF(study_val = 'ALL',d.validation_research_name IS NOT NULL, d.validation_research_name = study_val) 
								AND IF(population_val = 'ALL',i.population IS NOT NULL, i.population = population_val) 
								 AND IF(population_id_val = 'ALL',i.population_id IS NOT NULL, i.population_id = population_id_val) 
								AND IF(region_val = 'ALL',p.region IS NOT NULL, p.region = region_val)
								AND (d.allele_comment NOT LIKE '%error%' OR d.allele_comment IS NULL)
				) deeper
			GROUP BY deeper.family, deeper.relationship 
			ORDER BY deeper.family
	) AS t
	GROUP BY t.family

) AS sumary 
) AS ending
) AS last;

	RETURN inv_frequecy;
END IF;
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP FUNCTION IF EXISTS `inv_frequency_public_old` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8 */ ;
/*!50003 SET character_set_results = utf8 */ ;
/*!50003 SET collation_connection  = utf8_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_AUTO_VALUE_ON_ZERO' */ ;
DELIMITER ;;
CREATE DEFINER=`invfest_admin`@`localhost` FUNCTION `inv_frequency_public_old`(inv_id_val INT, population_val VARCHAR(255), region_val VARCHAR(255), study_val VARCHAR(255)) RETURNS varchar(255) CHARSET latin1
    SQL SECURITY INVOKER
BEGIN

DECLARE inv_frequecy VARCHAR(255) DEFAULT '0';

DECLARE chrm VARCHAR(255);
SELECT chr INTO chrm FROM inversions WHERE id = inv_id_val;

IF chrm = 'chrY' THEN
	RETURN "NA;NA;NA;NA";
ELSE

SELECT CONCAT_WS(';', last.indiv, last.alle, last.inv_frequecy,
				CASE  
					WHEN last.HW = "can not be compute" THEN "NA"
					WHEN	last.HW <= 0.4549364 THEN CONCAT('<font color="green">chi-square = ',last.HW,', p-value > 0.5</font>') 
					WHEN	last.HW >  0.4549364  AND last.HW <= 0.7083263  THEN CONCAT( '<font color="green">chi-square = ',last.HW, ', p-value > 0.4</font>') 
					WHEN	last.HW >  0.7083263  AND last.HW <= 1.0741942  THEN CONCAT( '<font color="green">chi-square = ',last.HW, ', p-value > 0.3</font>') 
					WHEN	last.HW >  1.0741942  AND last.HW <= 1.6423744  THEN CONCAT( '<font color="green">chi-square = ',last.HW, ', p-value > 0.2</font>') 
					WHEN	last.HW >  1.6423744  AND last.HW <= 2.7055435  THEN CONCAT( '<font color="green">chi-square = ',last.HW, ', p-value > 0.1</font>')
					WHEN	last.HW >  2.7055435  AND last.HW <= 3.8414588  THEN CONCAT( '<font color="green">chi-square = ',last.HW, ', p-value > 0.05</font>') 
					WHEN	last.HW >  3.8414588  AND last.HW <= 6.6348966  THEN CONCAT( '<font color="red">chi-square = ',last.HW, ', p-value < 0.05</font>') 
					WHEN	last.HW >  6.6348966  AND last.HW <= 7.8794386  THEN CONCAT( '<font color="red">chi-square = ',last.HW, ', p-value < 0.01</font>') 
					WHEN	last.HW >  7.8794386  AND last.HW <= 10.8275662 THEN CONCAT( '<font color="red">chi-square = ',last.HW, ', p-value < 0.005</font>') 
					WHEN	last.HW >  10.8275662 AND last.HW <= 12.1156651 THEN CONCAT( '<font color="red">chi-square = ',last.HW, ', p-value < 0.001</font>') 
					WHEN	last.HW >  12.1156651 AND last.HW <= 15.1367052 THEN CONCAT( '<font color="red">chi-square = ',last.HW, ', p-value < 0.0005</font>') 
					WHEN	last.HW >  15.1367052 THEN CONCAT( '<font color="red">chi-square = ',last.HW,', p-value < 0.0001</font>') 
				END
			)
INTO inv_frequecy
FROM(
SELECT CAST(ending.indiv AS CHAR) AS indiv , CAST(ending.alle AS CHAR) AS alle , CAST(ending.inv_frequecy AS CHAR) AS inv_frequecy,
				IF((ending.inv_frequecy = 0 OR ending.inv_frequecy = 1),"can not be compute",
				TRUNCATE( ((POW((ending.homo_inv - (POW(ending.inv_frequecy,2)*ending.indiv)),2)/(POW(ending.inv_frequecy,2)*ending.indiv))
				+
				(POW((ending.het - (2*ending.inv_frequecy*(1-ending.inv_frequecy)*ending.indiv)),2)/(2*ending.inv_frequecy*(1-ending.inv_frequecy)*ending.indiv))
				+
				(POW((ending.homo_std - (POW((1-ending.inv_frequecy),2)*ending.indiv)),2)/(POW((1-ending.inv_frequecy),2)*ending.indiv))) ,4))
				AS HW
FROM(
SELECT SUM( IF(sumary.mat_ascendent = 0 AND sumary.pat_ascendent = 0 , sumary.mat_son + sumary.pat_son + sumary.son + sumary.unrelated,
						IF(sumary.mat_ascendent != 0 OR sumary.pat_ascendent != 0 , sumary.mat_ascendent + sumary.pat_ascendent + sumary.unrelated,  0))
					) AS indiv, 
			 SUM( IF(sumary.mat_ascendent = 0 AND sumary.pat_ascendent = 0 , sumary.mat_inv_level1 + sumary.son_inv_level1 + sumary.pat_inv_level1 + sumary.unrelated_inv_allele,
						IF(sumary.mat_ascendent != 0 OR sumary.pat_ascendent != 0 , sumary.mat_inv_level0 + sumary.pat_inv_level0 + sumary.unrelated_inv_allele,  0))
						
						
					) AS alle,	
				(SUM( IF(sumary.mat_ascendent = 0 AND sumary.pat_ascendent = 0 , sumary.mat_inv_level1 + sumary.son_inv_level1 + sumary.unrelated_inv_allele,
				IF(sumary.mat_ascendent != 0 OR sumary.pat_ascendent != 0 , sumary.mat_inv_level0 + sumary.pat_inv_level0 + sumary.unrelated_inv_allele,0))
						
						
				))/
				(SUM( IF(sumary.mat_ascendent = 0 AND sumary.pat_ascendent = 0 , sumary.mat_sont_total_allele + sumary.son_total_allele + sumary.unrelated_total_allele, 
						IF(sumary.mat_ascendent != 0 OR sumary.pat_ascendent != 0 , sumary.mat_total_allele + sumary.pat_total_allele + sumary.unrelated_total_allele,0))
						
						
				)) AS inv_frequecy,
				SUM( IF(sumary.mat_ascendent = 0 AND sumary.pat_ascendent = 0 , sumary.mat_son_homo_inv + sumary.pat_son_homo_inv + sumary.son_homo_inv + sumary.unrelated_homo_inv,
						IF(sumary.mat_ascendent != 0 OR sumary.pat_ascendent != 0 , sumary.mat_ascendent_homo_inv + sumary.pat_ascendent_homo_inv + sumary.unrelated_homo_inv,  0))
					) AS homo_inv,
				SUM( IF(sumary.mat_ascendent = 0 AND sumary.pat_ascendent = 0 , sumary.mat_son_homo_std + sumary.pat_son_homo_std + sumary.son_homo_std + sumary.unrelated_homo_std,
						IF(sumary.mat_ascendent != 0 OR sumary.pat_ascendent != 0 , sumary.mat_ascendent_homo_std + sumary.pat_ascendent_homo_std + sumary.unrelated_homo_std,  0))
					) AS homo_std,
				SUM( IF(sumary.mat_ascendent = 0 AND sumary.pat_ascendent = 0 , sumary.mat_son_het + sumary.pat_son_het + sumary.son_het + sumary.unrelated_het,
						IF(sumary.mat_ascendent != 0 OR sumary.pat_ascendent != 0 , sumary.mat_ascendent_het + sumary.pat_ascendent_het + sumary.unrelated_het,  0))
					) AS het
				
FROM(
	SELECT t.name,t.chr, t.population, t.family, t.relationship, 
SUM(IF(t.relationship RLIKE '^m.+ther$' AND t.allele_level = 0, t.person,0)) AS mat_ascendent,
		SUM(IF(t.relationship RLIKE '^m.+ther$' AND t.allele_level = 0, t.homo_inv,0)) AS mat_ascendent_homo_inv,
		SUM(IF(t.relationship RLIKE '^m.+ther$' AND t.allele_level = 0, t.homo_std,0)) AS mat_ascendent_homo_std,
		SUM(IF(t.relationship RLIKE '^m.+ther$' AND t.allele_level = 0, t.het,0)) AS mat_ascendent_het,
		SUM(IF(t.relationship RLIKE '^m.+ther$' AND t.allele_level = 0, t.inv_allele, 0)) AS mat_inv_level0,
		SUM(IF(t.relationship RLIKE '^m.+ther$' AND t.allele_level = 0, t.total_allele, 0)) AS mat_total_allele,
		SUM(IF(t.relationship RLIKE '^[f,p].+ther$' AND t.allele_level = 0, t.person,0)) AS pat_ascendent,
		SUM(IF(t.relationship RLIKE '^[f,p].+ther$' AND t.allele_level = 0, t.homo_inv,0)) AS pat_ascendent_homo_inv,
		SUM(IF(t.relationship RLIKE '^[f,p].+ther$' AND t.allele_level = 0, t.homo_std,0)) AS pat_ascendent_homo_std,
		SUM(IF(t.relationship RLIKE '^[f,p].+ther$' AND t.allele_level = 0, t.het,0)) AS pat_ascendent_het,
		SUM(IF(t.relationship RLIKE '^[f,p].+ther$' AND t.allele_level = 0, t.inv_allele, 0)) AS pat_inv_level0,
		SUM(IF(t.relationship RLIKE '^[f,p].+ther$' AND t.allele_level = 0, t.total_allele, 0)) AS pat_total_allele,
		SUM(IF(t.relationship IN ('mother') AND t.allele_level = 1, t.person, 0)) AS mat_son,
		SUM(IF(t.relationship IN ('mother') AND t.allele_level = 1, t.homo_inv, 0)) AS mat_son_homo_inv,
		SUM(IF(t.relationship IN ('mother') AND t.allele_level = 1, t.homo_std, 0)) AS mat_son_homo_std,
		SUM(IF(t.relationship IN ('mother') AND t.allele_level = 1, t.het, 0)) AS mat_son_het,
		SUM(IF((t.relationship IN ('mother')) AND t.allele_level = 1, t.inv_allele, 0)) AS mat_inv_level1,
		SUM(IF(t.relationship IN ('mother') AND t.allele_level = 1, t.total_allele, 0)) AS mat_sont_total_allele,
		SUM(IF(t.relationship IN ('father') AND t.allele_level = 1, t.person, 0)) AS pat_son,
		SUM(IF(t.relationship IN ('father') AND t.allele_level = 1, t.homo_inv, 0)) AS pat_son_homo_inv,
		SUM(IF(t.relationship IN ('father') AND t.allele_level = 1, t.homo_std, 0)) AS pat_son_homo_std,
		SUM(IF(t.relationship IN ('father') AND t.allele_level = 1, t.het, 0)) AS pat_son_het,
		SUM(IF((t.relationship IN ('father')) AND t.allele_level = 1, t.inv_allele, 0)) AS pat_inv_level1,
		SUM(IF((t.relationship IN ('father')) AND t.allele_level = 1, t.total_allele, 0)) AS pat_sont_total_allele,
		SUM(IF(t.relationship IN ('child') AND t.allele_level = 1, t.person, 0)) AS son,
		SUM(IF(t.relationship IN ('child') AND t.allele_level = 1, t.homo_inv, 0)) AS son_homo_inv,
		SUM(IF(t.relationship IN ('child') AND t.allele_level = 1, t.homo_std, 0)) AS son_homo_std,
		SUM(IF(t.relationship IN ('child') AND t.allele_level = 1, t.het, 0)) AS son_het,
		SUM(IF((t.relationship IN ('child')) AND t.allele_level = 1, t.inv_allele, 0)) AS son_inv_level1,
		SUM(IF((t.relationship IN ('child')) AND t.allele_level = 1, t.total_allele, 0)) AS son_total_allele,
		SUM(IF(t.relationship IN ('unrelated') AND t.allele_level = 0, t.person, 0)) AS unrelated,
		SUM(IF(t.relationship IN ('unrelated') AND t.allele_level = 0, t.homo_inv, 0)) AS unrelated_homo_inv,
		SUM(IF(t.relationship IN ('unrelated') AND t.allele_level = 0, t.homo_std, 0)) AS unrelated_homo_std,
		SUM(IF(t.relationship IN ('unrelated') AND t.allele_level = 0, t.het, 0)) AS unrelated_het,
		SUM(IF((t.relationship IN ('unrelated')) AND t.allele_level = 0, t.inv_allele, 0)) AS unrelated_inv_allele,
		SUM(IF((t.relationship IN ('unrelated')) AND t.allele_level = 0, t.total_allele, 0)) AS unrelated_total_allele,
		SUM(t.person) AS indi  
	FROM(
		SELECT DISTINCTROW deeper.name, deeper.chr, deeper.population, deeper.family, deeper.relationship, deeper.allele_level
			,SUM(IF(deeper.genotype LIKE 'INV_INV',2,IF(deeper.genotype LIKE 'INV' OR deeper.genotype LIKE 'STD_INV',1,0))) AS inv_allele
			,SUM(IF(deeper.genotype LIKE '_______',2,IF(deeper.genotype LIKE '___',1,0))) AS total_allele
			,SUM(IF(deeper.genotype LIKE 'INV_INV',1,0)) AS homo_inv
			,SUM(IF(deeper.genotype LIKE 'STD_STD',1,0)) AS homo_std
			,SUM(IF(deeper.genotype LIKE 'STD_INV',1,0)) AS het
			,COUNT(DISTINCT deeper.`code`) person
		FROM( SELECT DISTINCTROW inv.name, inv.chr, i.`code`, i.population, i.family, i.relationship, i.allele_level, d.genotype
						FROM inversions AS inv JOIN individuals_detection AS d JOIN individuals AS i JOIN population AS p
							ON (inv.id = d.inversions_id AND d.individuals_id = i.id AND i.population = p.name)
						WHERE d.inversions_id = inv_id_val AND d.validation_research_name IS NOT NULL AND d.genotype IS NOT NULL AND d.genotype NOT IN ('NA', 'ND') 
								AND IF(study_val = 'ALL',d.validation_research_name IS NOT NULL, d.validation_research_name = study_val) 
								AND IF(population_val = 'ALL',i.population != 'unknown', i.population = population_val) 
								AND IF(region_val = 'ALL',p.region != 'unknown' , p.region = region_val)
								AND (d.allele_comment NOT LIKE '%error%' OR d.allele_comment IS NULL)
				) deeper
			GROUP BY deeper.family, IF(deeper.relationship RLIKE '^m.+ther$', 1, IF(deeper.relationship RLIKE '^[f,p].+ther$', 2, 3)), deeper.allele_level
			
	) AS t
	GROUP BY t.family
	
) AS sumary 
) AS ending
) AS last;

	RETURN inv_frequecy;
END IF;
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP FUNCTION IF EXISTS `inv_frequency_v1` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8 */ ;
/*!50003 SET character_set_results = utf8 */ ;
/*!50003 SET collation_connection  = utf8_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = '' */ ;
DELIMITER ;;
CREATE DEFINER=`invfest_admin`@`localhost` FUNCTION `inv_frequency_v1`(inv_id_val INT, population_val VARCHAR(255), region_val VARCHAR(255), study_val VARCHAR(255)) RETURNS varchar(255) CHARSET latin1
    SQL SECURITY INVOKER
BEGIN

DECLARE inv_frequecy VARCHAR(255) DEFAULT '0';

DECLARE chrm VARCHAR(255);
SELECT chr INTO chrm FROM inversions WHERE id = inv_id_val;

IF chrm = 'chrY' THEN
	RETURN "NA;NA;NA;NA";
ELSE

SELECT CONCAT_WS(';', last.indiv, last.alle, last.inv_frequecy,
				CASE  
					WHEN last.HW = "can not be compute" THEN "NA"
					WHEN	last.HW <= 0.4549364 THEN CONCAT('<font color="green">chi-square = ',last.HW,', p-value > 0.5</font>') 
					WHEN	last.HW >  0.4549364  AND last.HW <= 0.7083263  THEN CONCAT( '<font color="green">chi-square = ',last.HW, ', p-value > 0.4</font>') 
					WHEN	last.HW >  0.7083263  AND last.HW <= 1.0741942  THEN CONCAT( '<font color="green">chi-square = ',last.HW, ', p-value > 0.3</font>') 
					WHEN	last.HW >  1.0741942  AND last.HW <= 1.6423744  THEN CONCAT( '<font color="green">chi-square = ',last.HW, ', p-value > 0.2</font>') 
					WHEN	last.HW >  1.6423744  AND last.HW <= 2.7055435  THEN CONCAT( '<font color="green">chi-square = ',last.HW, ', p-value > 0.1</font>')
					WHEN	last.HW >  2.7055435  AND last.HW <= 3.8414588  THEN CONCAT( '<font color="green">chi-square = ',last.HW, ', p-value > 0.05</font>') 
					WHEN	last.HW >  3.8414588  AND last.HW <= 6.6348966  THEN CONCAT( '<font color="red">chi-square = ',last.HW, ', p-value < 0.05</font>') 
					WHEN	last.HW >  6.6348966  AND last.HW <= 7.8794386  THEN CONCAT( '<font color="red">chi-square = ',last.HW, ', p-value < 0.01</font>') 
					WHEN	last.HW >  7.8794386  AND last.HW <= 10.8275662 THEN CONCAT( '<font color="red">chi-square = ',last.HW, ', p-value < 0.005</font>') 
					WHEN	last.HW >  10.8275662 AND last.HW <= 12.1156651 THEN CONCAT( '<font color="red">chi-square = ',last.HW, ', p-value < 0.001</font>') 
					WHEN	last.HW >  12.1156651 AND last.HW <= 15.1367052 THEN CONCAT( '<font color="red">chi-square = ',last.HW, ', p-value < 0.0005</font>') 
					WHEN	last.HW >  15.1367052 THEN CONCAT( '<font color="red">chi-square = ',last.HW,', p-value < 0.0001</font>') 
				END
			)
INTO inv_frequecy
FROM(
SELECT CAST(ending.indiv AS CHAR) AS indiv , CAST(ending.alle AS CHAR) AS alle , CAST(ending.inv_frequecy AS CHAR) AS inv_frequecy,
				IF((ending.inv_frequecy = 0 OR ending.inv_frequecy = 1),"can not be compute",
				TRUNCATE( ((POW((ending.homo_inv - (POW(ending.inv_frequecy,2)*ending.indiv)),2)/(POW(ending.inv_frequecy,2)*ending.indiv))
				+
				(POW((ending.het - (2*ending.inv_frequecy*(1-ending.inv_frequecy)*ending.indiv)),2)/(2*ending.inv_frequecy*(1-ending.inv_frequecy)*ending.indiv))
				+
				(POW((ending.homo_std - (POW((1-ending.inv_frequecy),2)*ending.indiv)),2)/(POW((1-ending.inv_frequecy),2)*ending.indiv))) ,4))
				AS HW
FROM(
SELECT SUM( IF(sumary.mat_ascendent = 0 AND sumary.pat_ascendent = 0 , sumary.mat_son + sumary.pat_son + sumary.son + sumary.unrelated,
						IF(sumary.mat_ascendent != 0 OR sumary.pat_ascendent != 0 , sumary.mat_ascendent + sumary.pat_ascendent + sumary.unrelated,  0))
					) AS indiv, 
			 SUM( IF(sumary.mat_ascendent = 0 AND sumary.pat_ascendent = 0 , sumary.mat_inv_level1 + sumary.son_inv_level1 + sumary.pat_inv_level1 + sumary.unrelated_inv_allele,
						IF(sumary.mat_ascendent != 0 OR sumary.pat_ascendent != 0 , sumary.mat_inv_level0 + sumary.pat_inv_level0 + sumary.unrelated_inv_allele,  0))
						
						
					) AS alle,	
				(SUM( IF(sumary.mat_ascendent = 0 AND sumary.pat_ascendent = 0 , sumary.mat_inv_level1 + sumary.son_inv_level1 + sumary.unrelated_inv_allele,
				IF(sumary.mat_ascendent != 0 OR sumary.pat_ascendent != 0 , sumary.mat_inv_level0 + sumary.pat_inv_level0 + sumary.unrelated_inv_allele,0))
						
						
				))/
				(SUM( IF(sumary.mat_ascendent = 0 AND sumary.pat_ascendent = 0 , sumary.mat_sont_total_allele + sumary.son_total_allele + sumary.unrelated_total_allele, 
						IF(sumary.mat_ascendent != 0 OR sumary.pat_ascendent != 0 , sumary.mat_total_allele + sumary.pat_total_allele + sumary.unrelated_total_allele,0))
						
						
				)) AS inv_frequecy,
				SUM( IF(sumary.mat_ascendent = 0 AND sumary.pat_ascendent = 0 , sumary.mat_son_homo_inv + sumary.pat_son_homo_inv + sumary.son_homo_inv + sumary.unrelated_homo_inv,
						IF(sumary.mat_ascendent != 0 OR sumary.pat_ascendent != 0 , sumary.mat_ascendent_homo_inv + sumary.pat_ascendent_homo_inv + sumary.unrelated_homo_inv,  0))
					) AS homo_inv,
				SUM( IF(sumary.mat_ascendent = 0 AND sumary.pat_ascendent = 0 , sumary.mat_son_homo_std + sumary.pat_son_homo_std + sumary.son_homo_std + sumary.unrelated_homo_std,
						IF(sumary.mat_ascendent != 0 OR sumary.pat_ascendent != 0 , sumary.mat_ascendent_homo_std + sumary.pat_ascendent_homo_std + sumary.unrelated_homo_std,  0))
					) AS homo_std,
				SUM( IF(sumary.mat_ascendent = 0 AND sumary.pat_ascendent = 0 , sumary.mat_son_het + sumary.pat_son_het + sumary.son_het + sumary.unrelated_het,
						IF(sumary.mat_ascendent != 0 OR sumary.pat_ascendent != 0 , sumary.mat_ascendent_het + sumary.pat_ascendent_het + sumary.unrelated_het,  0))
					) AS het
				
FROM(
	SELECT t.name,t.chr, t.population, t.family, t.relationship, 
SUM(IF(t.relationship RLIKE '^m.+ther$' AND t.allele_level = 0, t.person,0)) AS mat_ascendent,
		SUM(IF(t.relationship RLIKE '^m.+ther$' AND t.allele_level = 0, t.homo_inv,0)) AS mat_ascendent_homo_inv,
		SUM(IF(t.relationship RLIKE '^m.+ther$' AND t.allele_level = 0, t.homo_std,0)) AS mat_ascendent_homo_std,
		SUM(IF(t.relationship RLIKE '^m.+ther$' AND t.allele_level = 0, t.het,0)) AS mat_ascendent_het,
		SUM(IF(t.relationship RLIKE '^m.+ther$' AND t.allele_level = 0, t.inv_allele, 0)) AS mat_inv_level0,
		SUM(IF(t.relationship RLIKE '^m.+ther$' AND t.allele_level = 0, t.total_allele, 0)) AS mat_total_allele,
		SUM(IF(t.relationship RLIKE '^[f,p].+ther$' AND t.allele_level = 0, t.person,0)) AS pat_ascendent,
		SUM(IF(t.relationship RLIKE '^[f,p].+ther$' AND t.allele_level = 0, t.homo_inv,0)) AS pat_ascendent_homo_inv,
		SUM(IF(t.relationship RLIKE '^[f,p].+ther$' AND t.allele_level = 0, t.homo_std,0)) AS pat_ascendent_homo_std,
		SUM(IF(t.relationship RLIKE '^[f,p].+ther$' AND t.allele_level = 0, t.het,0)) AS pat_ascendent_het,
		SUM(IF(t.relationship RLIKE '^[f,p].+ther$' AND t.allele_level = 0, t.inv_allele, 0)) AS pat_inv_level0,
		SUM(IF(t.relationship RLIKE '^[f,p].+ther$' AND t.allele_level = 0, t.total_allele, 0)) AS pat_total_allele,
		SUM(IF(t.relationship IN ('mother') AND t.allele_level = 1, t.person, 0)) AS mat_son,
		SUM(IF(t.relationship IN ('mother') AND t.allele_level = 1, t.homo_inv, 0)) AS mat_son_homo_inv,
		SUM(IF(t.relationship IN ('mother') AND t.allele_level = 1, t.homo_std, 0)) AS mat_son_homo_std,
		SUM(IF(t.relationship IN ('mother') AND t.allele_level = 1, t.het, 0)) AS mat_son_het,
		SUM(IF((t.relationship IN ('mother')) AND t.allele_level = 1, t.inv_allele, 0)) AS mat_inv_level1,
		SUM(IF(t.relationship IN ('mother') AND t.allele_level = 1, t.total_allele, 0)) AS mat_sont_total_allele,
		SUM(IF(t.relationship IN ('father') AND t.allele_level = 1, t.person, 0)) AS pat_son,
		SUM(IF(t.relationship IN ('father') AND t.allele_level = 1, t.homo_inv, 0)) AS pat_son_homo_inv,
		SUM(IF(t.relationship IN ('father') AND t.allele_level = 1, t.homo_std, 0)) AS pat_son_homo_std,
		SUM(IF(t.relationship IN ('father') AND t.allele_level = 1, t.het, 0)) AS pat_son_het,
		SUM(IF((t.relationship IN ('father')) AND t.allele_level = 1, t.inv_allele, 0)) AS pat_inv_level1,
		SUM(IF((t.relationship IN ('father')) AND t.allele_level = 1, t.total_allele, 0)) AS pat_sont_total_allele,
		SUM(IF(t.relationship IN ('child') AND t.allele_level = 1, t.person, 0)) AS son,
		SUM(IF(t.relationship IN ('child') AND t.allele_level = 1, t.homo_inv, 0)) AS son_homo_inv,
		SUM(IF(t.relationship IN ('child') AND t.allele_level = 1, t.homo_std, 0)) AS son_homo_std,
		SUM(IF(t.relationship IN ('child') AND t.allele_level = 1, t.het, 0)) AS son_het,
		SUM(IF((t.relationship IN ('child')) AND t.allele_level = 1, t.inv_allele, 0)) AS son_inv_level1,
		SUM(IF((t.relationship IN ('child')) AND t.allele_level = 1, t.total_allele, 0)) AS son_total_allele,
		SUM(IF(t.relationship IN ('unrelated') AND t.allele_level = 0, t.person, 0)) AS unrelated,
		SUM(IF(t.relationship IN ('unrelated') AND t.allele_level = 0, t.homo_inv, 0)) AS unrelated_homo_inv,
		SUM(IF(t.relationship IN ('unrelated') AND t.allele_level = 0, t.homo_std, 0)) AS unrelated_homo_std,
		SUM(IF(t.relationship IN ('unrelated') AND t.allele_level = 0, t.het, 0)) AS unrelated_het,
		SUM(IF((t.relationship IN ('unrelated')) AND t.allele_level = 0, t.inv_allele, 0)) AS unrelated_inv_allele,
		SUM(IF((t.relationship IN ('unrelated')) AND t.allele_level = 0, t.total_allele, 0)) AS unrelated_total_allele,
		SUM(t.person) AS indi  
	FROM(
		SELECT DISTINCTROW deeper.name, deeper.chr, deeper.population, deeper.family, deeper.relationship, deeper.allele_level
			,SUM(IF(deeper.genotype LIKE 'INV_INV',2,IF(deeper.genotype LIKE 'INV' OR deeper.genotype LIKE 'STD_INV',1,0))) AS inv_allele
			,SUM(IF(deeper.genotype LIKE '_______',2,IF(deeper.genotype LIKE '___',1,0))) AS total_allele
			,SUM(IF(deeper.genotype LIKE 'INV_INV',1,0)) AS homo_inv
			,SUM(IF(deeper.genotype LIKE 'STD_STD',1,0)) AS homo_std
			,SUM(IF(deeper.genotype LIKE 'STD_INV',1,0)) AS het
			,COUNT(DISTINCT deeper.`code`) person
		FROM( SELECT DISTINCTROW inv.name, inv.chr, i.`code`, i.population, i.family, i.relationship, i.allele_level, d.genotype
						FROM inversions AS inv JOIN individuals_detection AS d JOIN individuals AS i JOIN population AS p
							ON (inv.id = d.inversions_id AND d.individuals_id = i.id AND i.population = p.name)
						WHERE d.inversions_id = inv_id_val AND d.validation_research_name IS NOT NULL AND d.genotype IS NOT NULL AND d.genotype NOT IN ('NA', 'ND') 
								AND IF(study_val = 'ALL',d.validation_research_name IS NOT NULL, d.validation_research_name = study_val) 
								AND IF(population_val = 'ALL',i.population != 'unknown', i.population = population_val) 
								AND IF(region_val = 'ALL',p.region != 'unknown' , p.region = region_val)
								AND (d.allele_comment NOT LIKE '%error%' OR d.allele_comment IS NULL)
				) deeper
			GROUP BY deeper.family, IF(deeper.relationship RLIKE '^m.+ther$', 1, IF(deeper.relationship RLIKE '^[f,p].+ther$', 2, 3)), deeper.allele_level
			
	) AS t
	GROUP BY t.family
	
) AS sumary 
) AS ending
) AS last;

	RETURN inv_frequecy;
END IF;
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP FUNCTION IF EXISTS `merge_inv` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8 */ ;
/*!50003 SET character_set_results = utf8 */ ;
/*!50003 SET collation_connection  = utf8_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = '' */ ;
DELIMITER ;;
CREATE DEFINER=`invfest_admin`@`localhost` FUNCTION `merge_inv`(old_Inv_id_list_val VARCHAR(255), user_id_val INT) RETURNS int(11)
BEGIN


DECLARE previous_value_val text;
DECLARE newer_value_val text;
DECLARE task_val text;

DECLARE old_status_val VARCHAR(255);
DECLARE new_status_val VARCHAR(255);

DECLARE new_Inv_name VARCHAR(255);
DECLARE history_cause_val VARCHAR(255);


DECLARE old_Inv_name_list VARCHAR(255);

DECLARE new_amount_merged_val INT;
DECLARE inv_id_merged_val INT; 

DECLARE new_Inv_id_val INT DEFAULT 0;

DECLARE num_pred_on_inv INT DEFAULT 0; 
DECLARE loop_cntr_pred_on_inv  INT DEFAULT 0;

DECLARE total_pred_merged VARCHAR(255);

DECLARE same_group INT DEFAULT 0; 

DECLARE loop_cntr INT DEFAULT 0;
DECLARE num_rows INT DEFAULT 0;

DECLARE research_name_val VARCHAR(255);
DECLARE research_id_val INT;
DECLARE inv_id_val INT;
DECLARE chr_val VARCHAR(255);
DECLARE BP1s_val INT;
DECLARE BP1e_val INT;
DECLARE BP2s_val INT;
DECLARE BP2e_val INT;
DECLARE RBP1s_val INT;
DECLARE RBP1e_val INT;
DECLARE RBP2s_val INT;
DECLARE RBP2e_val INT;
DECLARE pstatus_val VARCHAR(255);
DECLARE paccuracy_val VARCHAR(255);
DECLARE pchecking_val VARCHAR(255);
DECLARE pcomments_val TEXT;
DECLARE psupport_val INT;
DECLARE psupport_bp2_val INT;
DECLARE pscore1_val FLOAT;
DECLARE pscore2_val FLOAT;

DECLARE new_predi_id_val INT;
DECLARE new_vali_id_val INT;

DECLARE vali_id_val VARCHAR(255);
DECLARE method_val VARCHAR(255);
DECLARE status_val VARCHAR(255);
DECLARE experimental_conditions_val VARCHAR(255);
DECLARE primers_val VARCHAR(255);
DECLARE comment_val TEXT ;
DECLARE checked_val VARCHAR(255);


DECLARE vali_cur CURSOR FOR
	SELECT DISTINCT id, research_name, inv_id, method, status, experimental_conditions, primers, comment, checked
	FROM validation
	WHERE FIND_IN_SET(inv_id, old_Inv_id_list_val) > 0;
	
DECLARE predi_cur CURSOR FOR
	SELECT DISTINCT inv_id, research_name, research_id, chr, BP1s, BP1e, BP2s, BP2e, RBP1s, RBP1e, RBP2s, RBP2e, status, accuracy, checking, comments, support, support_bp2, score1, score2
	FROM predictions
	WHERE FIND_IN_SET(inv_id, old_Inv_id_list_val) > 0
	GROUP BY research_name, research_id;






IF old_Inv_id_list_val != 'NA' THEN


	INSERT INTO inversions	(chr, range_start, range_end, size, detected_amount)
								SELECT chr, MIN(range_start), MAX(range_end), MIN(size), MAX(detected_amount)
								FROM inversions WHERE FIND_IN_SET(id, old_Inv_id_list_val) > 0;
	

	SELECT LAST_INSERT_ID() INTO new_Inv_id_val; 
	SET task_val = CONCAT('INSERT new inversions ');
	CALL  save_log(user_id_val, task_val, "none", new_Inv_id_val);
	


OPEN  predi_cur;
		SELECT FOUND_ROWS() INTO num_rows;
		SET new_amount_merged_val = num_rows;
			WHILE loop_cntr < num_rows DO 
				FETCH  predi_cur INTO inv_id_val, research_name_val, research_id_val, chr_val, BP1s_val, BP1e_val, BP2s_val, BP2e_val, RBP1s_val, RBP1e_val, RBP2s_val, RBP2e_val, pstatus_val, paccuracy_val, pchecking_val, pcomments_val, psupport_val, psupport_bp2_val, pscore1_val, pscore2_val;
				INSERT INTO predictions (inv_id, research_name, research_id, chr, BP1s, BP1e, BP2s, BP2e, RBP1s, RBP1e, RBP2s, RBP2e, status, accuracy, checking, comments, support, support_bp2, score1, score2) 
					   VALUE (new_Inv_id_val, research_name_val, research_id_val, chr_val, BP1s_val, BP1e_val, BP2s_val, BP2e_val, RBP1s_val, RBP1e_val, RBP2s_val, RBP2e_val, pstatus_val, paccuracy_val, pchecking_val, pcomments_val, psupport_val, psupport_bp2_val, pscore1_val, pscore2_val);


		SELECT LAST_INSERT_ID() INTO new_predi_id_val; 

				INSERT INTO individuals_detection (individuals_id, inversions_id, prediction_research_id, prediction_research_name, prediction_id)
					SELECT ind_det.individuals_id, new_Inv_id_val, ind_det.prediction_research_id, ind_det.prediction_research_name, new_predi_id_val  FROM individuals_detection AS ind_det
					WHERE ind_det.inversions_id = inv_id_val AND ind_det.prediction_research_id = research_id_val AND ind_det.prediction_research_name = research_name_val;
											
				SET loop_cntr = loop_cntr + 1; 
			END WHILE; 
CLOSE predi_cur;

SET loop_cntr = 0;
SET num_rows = 0;


OPEN  vali_cur;
		SELECT FOUND_ROWS() INTO num_rows;
			WHILE loop_cntr < num_rows DO 
				FETCH  vali_cur INTO vali_id_val, research_name_val , inv_id_val, method_val, status_val, experimental_conditions_val, primers_val, comment_val, checked_val;
				INSERT INTO validation	(research_name, inv_id, method, status, experimental_conditions, primers, comment, checked) 
					   VALUE (research_name_val , new_Inv_id_val, method_val, status_val, experimental_conditions_val, primers_val, comment_val, checked_val);


		SELECT LAST_INSERT_ID() INTO new_vali_id_val; 
				
				INSERT INTO individuals_detection (individuals_id, inversions_id, validation_id, validation_research_name, genotype, allele_comment)
					SELECT ind_det.individuals_id, new_Inv_id_val, new_vali_id_val, ind_det.validation_research_name, ind_det.genotype, ind_det.allele_comment  FROM individuals_detection AS ind_det 
					WHERE ind_det.inversions_id = inv_id_val AND ind_det.validation_id = vali_id_val AND ind_det.validation_research_name = research_name_val;
											
				SET loop_cntr = loop_cntr + 1; 
			END WHILE; 
CLOSE vali_cur;



	SELECT CONCAT('HsInv', SUBSTRING(`name`, -4) +1) INTO  new_Inv_name
	FROM inversions WHERE `name` LIKE 'HsInv%' ORDER BY `name` DESC  LIMIT 1;
	
	SELECT GROUP_CONCAT(status) INTO old_status_val FROM inversions WHERE FIND_IN_SET(id, old_Inv_id_list_val) > 0;
		
	IF old_status_val LIKE ('%TRUE%') AND old_status_val NOT LIKE('%FALSE%') THEN SET new_status_val = 'TRUE';
	ELSEIF old_status_val LIKE ('%FALSE%') AND old_status_val NOT LIKE ('%TRUE%') THEN SET new_status_val = 'FALSE';
	ELSE SET new_status_val = 'ND';
	END IF;	
	
	UPDATE inversions SET `name` =  new_Inv_name,  detected_amount = new_amount_merged_val, status = new_status_val WHERE id = new_Inv_id_val;
	

	SELECT GROUP_CONCAT( CONCAT('<a href="report.php?q=', CAST(id AS CHAR), '">', `name`,'</a>') SEPARATOR ' and ') INTO history_cause_val FROM inversions WHERE FIND_IN_SET(id, old_Inv_id_list_val) > 0;
	SET history_cause_val = CONCAT( history_cause_val, 'merge into <a href="report.php?q=', CAST(new_Inv_id_val AS CHAR), '">', new_Inv_name, '</a>');
	
	INSERT INTO inversion_history (previous_inv_id, new_inv_id, cause)
								SELECT id, new_Inv_id_val, history_cause_val FROM inversions WHERE FIND_IN_SET(id, old_Inv_id_list_val) > 0;
		
	SELECT GROUP_CONCAT(status SEPARATOR '; ') INTO previous_value_val FROM inversions WHERE FIND_IN_SET(id, old_Inv_id_list_val) > 0;
		
	UPDATE inversions SET status =  "withdrawn" WHERE FIND_IN_SET(id, old_Inv_id_list_val) > 0;
		
	SELECT GROUP_CONCAT(status SEPARATOR '; ') INTO newer_value_val FROM inversions WHERE FIND_IN_SET(id, old_Inv_id_list_val) > 0;
		
	SET task_val = CONCAT('UPDATE status of inv ', old_Inv_id_list_val);
	CALL  save_log(user_id_val, task_val, previous_value_val, newer_value_val);
	

	CALL update_BP(new_Inv_id_val, NULL, user_id_val);

RETURN new_Inv_id_val ; 

ELSE

RETURN 0; 
	
END IF;

END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP FUNCTION IF EXISTS `merge_inv_newmerge` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8 */ ;
/*!50003 SET character_set_results = utf8 */ ;
/*!50003 SET collation_connection  = utf8_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = '' */ ;
DELIMITER ;;
CREATE DEFINER=`invfest_admin`@`localhost` FUNCTION `merge_inv_newmerge`(old_Inv_id_list_val VARCHAR(255), user_id_val INT) RETURNS int(11)
BEGIN

DECLARE previous_value_val text;
DECLARE newer_value_val text;
DECLARE task_val text;

DECLARE old_status_val VARCHAR(255);
DECLARE new_status_val VARCHAR(255);

DECLARE new_Inv_name VARCHAR(255);
DECLARE history_cause_val VARCHAR(255);


DECLARE old_Inv_name_list VARCHAR(255);

DECLARE new_amount_merged_val INT;
DECLARE inv_id_merged_val INT; 

DECLARE new_Inv_id_val INT DEFAULT 0;

DECLARE num_pred_on_inv INT DEFAULT 0; 
DECLARE loop_cntr_pred_on_inv  INT DEFAULT 0;

DECLARE total_pred_merged VARCHAR(255);

DECLARE same_group INT DEFAULT 0; 

DECLARE loop_cntr INT DEFAULT 0;
DECLARE num_rows INT DEFAULT 0;

DECLARE research_name_val VARCHAR(255);
DECLARE research_id_val INT;
DECLARE inv_id_val INT;
DECLARE chr_val VARCHAR(255);
DECLARE BP1s_val INT;
DECLARE BP1e_val INT;
DECLARE BP2s_val INT;
DECLARE BP2e_val INT;
DECLARE RBP1s_val INT;
DECLARE RBP1e_val INT;
DECLARE RBP2s_val INT;
DECLARE RBP2e_val INT;
DECLARE pstatus_val VARCHAR(255);
DECLARE paccuracy_val VARCHAR(255);
DECLARE pchecking_val VARCHAR(255);
DECLARE pcomments_val TEXT;
DECLARE psupport_val INT;
DECLARE psupport_bp2_val INT;
DECLARE pscore1_val FLOAT;
DECLARE pscore2_val FLOAT;

DECLARE new_predi_id_val INT;
DECLARE new_vali_id_val INT;

DECLARE vali_id_val VARCHAR(255);
DECLARE method_val VARCHAR(255);
DECLARE status_val VARCHAR(255);
DECLARE experimental_conditions_val VARCHAR(255);
DECLARE primers_val VARCHAR(255);
DECLARE comment_val TEXT ;
DECLARE checked_val VARCHAR(255);


DECLARE vali_cur CURSOR FOR
	SELECT DISTINCT id, research_name, inv_id, method, status, experimental_conditions, primers, comment, checked
	FROM validation
	WHERE FIND_IN_SET(inv_id, old_Inv_id_list_val) > 0;
	
DECLARE predi_cur CURSOR FOR
	SELECT DISTINCT inv_id, research_name, research_id, chr, BP1s, BP1e, BP2s, BP2e, RBP1s, RBP1e, RBP2s, RBP2e, status, accuracy, checking, comments, support, support_bp2, score1, score2
	FROM predictions
	WHERE FIND_IN_SET(inv_id, old_Inv_id_list_val) > 0
	GROUP BY research_name, research_id;




SET FOREIGN_KEY_CHECKS=0;


IF old_Inv_id_list_val != 'NA' THEN


	INSERT INTO inversions	(chr, range_start, range_end, size, detected_amount)
								SELECT chr, MIN(range_start), MAX(range_end), MIN(size), MAX(detected_amount)
								FROM inversions WHERE FIND_IN_SET(id, old_Inv_id_list_val) > 0;
	

	SELECT LAST_INSERT_ID() INTO new_Inv_id_val; 
	SET task_val = CONCAT('INSERT new inversions ');
	CALL  save_log(user_id_val, task_val, "none", new_Inv_id_val);
	


OPEN  predi_cur;
		SELECT FOUND_ROWS() INTO num_rows;
		SET new_amount_merged_val = num_rows;
			WHILE loop_cntr < num_rows DO 
				FETCH  predi_cur INTO inv_id_val, research_name_val, research_id_val, chr_val, BP1s_val, BP1e_val, BP2s_val, BP2e_val, RBP1s_val, RBP1e_val, RBP2s_val, RBP2e_val, pstatus_val, paccuracy_val, pchecking_val, pcomments_val, psupport_val, psupport_bp2_val, pscore1_val, pscore2_val;
				INSERT INTO predictions (inv_id, research_name, research_id, chr, BP1s, BP1e, BP2s, BP2e, RBP1s, RBP1e, RBP2s, RBP2e, status, accuracy, checking, comments, support, support_bp2, score1, score2) 
					   VALUE (new_Inv_id_val, research_name_val, research_id_val, chr_val, BP1s_val, BP1e_val, BP2s_val, BP2e_val, RBP1s_val, RBP1e_val, RBP2s_val, RBP2e_val, pstatus_val, paccuracy_val, pchecking_val, pcomments_val, psupport_val, psupport_bp2_val, pscore1_val, pscore2_val);


		SELECT LAST_INSERT_ID() INTO new_predi_id_val; 

				INSERT INTO individuals_detection (individuals_id, inversions_id, prediction_research_id, prediction_research_name, prediction_id)
					SELECT ind_det.individuals_id, new_Inv_id_val, ind_det.prediction_research_id, ind_det.prediction_research_name, new_predi_id_val  FROM individuals_detection AS ind_det
					WHERE ind_det.inversions_id = inv_id_val AND ind_det.prediction_research_id = research_id_val AND ind_det.prediction_research_name = research_name_val;
											
				SET loop_cntr = loop_cntr + 1; 
			END WHILE; 
CLOSE predi_cur;

SET loop_cntr = 0;
SET num_rows = 0;


OPEN  vali_cur;
		SELECT FOUND_ROWS() INTO num_rows;
			WHILE loop_cntr < num_rows DO 
				FETCH  vali_cur INTO vali_id_val, research_name_val , inv_id_val, method_val, status_val, experimental_conditions_val, primers_val, comment_val, checked_val;
				INSERT INTO validation	(research_name, inv_id, method, status, experimental_conditions, primers, comment, checked) 
					   VALUE (research_name_val , new_Inv_id_val, method_val, status_val, experimental_conditions_val, primers_val, comment_val, checked_val);


		SELECT LAST_INSERT_ID() INTO new_vali_id_val; 
				
				INSERT INTO individuals_detection (individuals_id, inversions_id, validation_id, validation_research_name, genotype, allele_comment)
					SELECT ind_det.individuals_id, new_Inv_id_val, new_vali_id_val, ind_det.validation_research_name, ind_det.genotype, ind_det.allele_comment  FROM individuals_detection AS ind_det 
					WHERE ind_det.inversions_id = inv_id_val AND ind_det.validation_id = vali_id_val AND ind_det.validation_research_name = research_name_val;
											
				SET loop_cntr = loop_cntr + 1; 
			END WHILE; 
CLOSE vali_cur;



	SELECT CONCAT('HsInv', SUBSTRING(`name`, -4) +1) INTO  new_Inv_name
	FROM inversions WHERE `name` LIKE 'HsInv%' ORDER BY `name` DESC  LIMIT 1;
	
	SELECT GROUP_CONCAT(status) INTO old_status_val FROM inversions WHERE FIND_IN_SET(id, old_Inv_id_list_val) > 0;
		
	IF old_status_val LIKE ('%TRUE%') AND old_status_val NOT LIKE('%FALSE%') THEN SET new_status_val = 'TRUE';
	ELSEIF old_status_val LIKE ('%FALSE%') AND old_status_val NOT LIKE ('%TRUE%') THEN SET new_status_val = 'FALSE';
	ELSE SET new_status_val = 'ND';
	END IF;	
	
	UPDATE inversions SET `name` =  new_Inv_name,  detected_amount = new_amount_merged_val, status = new_status_val WHERE id = new_Inv_id_val;
	

	SELECT GROUP_CONCAT( CONCAT('<a href="report.php?q=', CAST(id AS CHAR), '">', `name`,'</a>') SEPARATOR ' and ') INTO history_cause_val FROM inversions WHERE FIND_IN_SET(id, old_Inv_id_list_val) > 0;
	SET history_cause_val = CONCAT( history_cause_val, ' merge into <a href="report.php?q=', CAST(new_Inv_id_val AS CHAR), '">', new_Inv_name, '</a>');
	
	INSERT INTO inversion_history (previous_inv_id, new_inv_id, cause)
								SELECT id, new_Inv_id_val, history_cause_val FROM inversions WHERE FIND_IN_SET(id, old_Inv_id_list_val) > 0;
		
	SELECT GROUP_CONCAT(status SEPARATOR '; ') INTO previous_value_val FROM inversions WHERE FIND_IN_SET(id, old_Inv_id_list_val) > 0;
		
	UPDATE inversions SET status =  "withdrawn" WHERE FIND_IN_SET(id, old_Inv_id_list_val) > 0;
		
	SELECT GROUP_CONCAT(status SEPARATOR '; ') INTO newer_value_val FROM inversions WHERE FIND_IN_SET(id, old_Inv_id_list_val) > 0;
		
	SET task_val = CONCAT('UPDATE status of inv ', old_Inv_id_list_val);
	CALL  save_log(user_id_val, task_val, previous_value_val, newer_value_val);
	

	CALL update_BP_newmerge(new_Inv_id_val, NULL, user_id_val);

RETURN new_Inv_id_val ; 

ELSE

RETURN 0; 
	
END IF;

SET FOREIGN_KEY_CHECKS=1;


END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP PROCEDURE IF EXISTS `add_BP` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8 */ ;
/*!50003 SET character_set_results = utf8 */ ;
/*!50003 SET collation_connection  = utf8_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = '' */ ;
DELIMITER ;;
CREATE DEFINER=`invfest_admin`@`localhost` PROCEDURE `add_BP`(IN validation_id_val INT, IN `inversion_id_val` int, IN chr_val  VARCHAR(255), IN bp1s_val INT, IN bp1e_val INT, IN bp2s_val INT, IN bp2e_val INT, IN description_val  TEXT, IN user_id_val INT)
    SQL SECURITY INVOKER
BEGIN
	
	# VARIABLES 

	DECLARE next_date DATE ; 
    DECLARE new_BP_id_val  INT DEFAULT 0;
    
    DECLARE previous_value_val text;
	DECLARE newer_value_val text;
	DECLARE task_val text;
	
# START
	#SELECT CURRENT_TIMESTAMP() INTO next_date;
 	#SET next_date = CURRENT_TIMESTAMP();
  	
     # Insert breakpoint
     INSERT INTO breakpoints	(inv_id, chr, bp1_start, bp1_end, bp2_start, bp2_end, definition_method, description, date, researcher)
 		VALUES (inversion_id_val, chr_val, bp1s_val, bp1e_val,  bp2s_val, bp2e_val, "manual curation", description_val, CURRENT_TIMESTAMP(), '');	
  		
 	SELECT LAST_INSERT_ID() INTO new_BP_id_val;
  	SET task_val = CONCAT('INSERT new breakpoints of inv ',inversion_id_val);
  	CALL  save_log(user_id_val, task_val, "none", new_BP_id_val);
  		
     # ADD extra info
  	CALL  get_inv_gene_realtion(new_BP_id_val);
  	CALL  get_SD_in_BP (new_BP_id_val);
 
 	# If the manual curation comes from a validation
     IF validation_id_val != "NA" THEN
		SELECT bp_id
 			INTO previous_value_val
 			FROM `validation`
 			WHERE id = validation_id_val;
 		UPDATE `validation`
 			SET bp_id = new_BP_id_val 
 			WHERE id = validation_id_val;
 		SELECT bp_id
			INTO newer_value_val
 			FROM `validation`
 			WHERE id = validation_id_val;
          
  		SET task_val = CONCAT('UPDATE breakpoint id of validation ',validation_id_val);
  		CALL  save_log(user_id_val, task_val, previous_value_val, newer_value_val);
      END IF;
     
 	# The range and accuracy are updated in update_BP
     CALL update_BP(inversion_id_val,  description_val, 'VALIDATION', user_id_val);
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP PROCEDURE IF EXISTS `add_evolutionary_info` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8 */ ;
/*!50003 SET character_set_results = utf8 */ ;
/*!50003 SET collation_connection  = utf8_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = '' */ ;
DELIMITER ;;
CREATE DEFINER=`invfest_admin`@`localhost` PROCEDURE `add_evolutionary_info`(IN key_val INT, IN inv_id_val INT, IN table_val VARCHAR(255),  IN info_val VARCHAR(255), IN method_val VARCHAR(255), IN source_val VARCHAR(255), IN user_id_val INT)
    SQL SECURITY INVOKER
BEGIN

DECLARE previous_value_val text;
DECLARE newer_value_val INT;
DECLARE task_val text;

DECLARE potential_orientation text;



CASE table_val   
	WHEN 'inv_origin' THEN 
		IF NOT EXISTS (SELECT * FROM inv_origin WHERE inv_id = inv_id_val AND origin = info_val AND method = method_val AND source = source_val) THEN 
			INSERT INTO inv_origin ( inv_id, origin, method, source ) VALUES(inv_id_val, info_val, `method_val`, `source_val`);
			SELECT LAST_INSERT_ID() INTO newer_value_val;
			SET task_val = CONCAT('INSERT new inv_origin of inv ',inv_id_val);
			CALL  save_log(user_id_val, task_val, "none", newer_value_val);
			
			SELECT evo_origin INTO previous_value_val FROM inversions WHERE id = inv_id_val;
			UPDATE inversions SET evo_origin = info_val WHERE id = inv_id_val;
			SET task_val = CONCAT('UPDATE evo_origin of inv ',inv_id_val);
			CALL  save_log(user_id_val, task_val, previous_value_val, info_val);


		END IF;
	WHEN 'inv_age' THEN 
		IF NOT EXISTS (SELECT * FROM inv_age WHERE inv_id = inv_id_val AND age = info_val AND method = `method_val` AND source = `source_val`) THEN 
			INSERT INTO inv_age (inv_id, age, method, source) VALUES(inv_id_val, info_val, `method_val`, `source_val`);
			SELECT LAST_INSERT_ID() INTO newer_value_val;
			SET task_val = CONCAT('INSERT new inv_age of inv ',inv_id_val);
			CALL  save_log(user_id_val, task_val, "none", newer_value_val);
		END IF;
	WHEN 'inversions_in_species' THEN 
		IF NOT EXISTS (SELECT * FROM inversions_in_species WHERE species_id = key_val AND inversions_id = inv_id_val AND orientation = info_val AND method = method_val AND source = source_val) THEN 
			INSERT INTO inversions_in_species (species_id, inversions_id, orientation, method, source) VALUES(key_val, inv_id_val, info_val, `method_val`, `source_val`);
			SELECT LAST_INSERT_ID() INTO newer_value_val;
			SET task_val = CONCAT('INSERT new inversions_in_species of inv ',inv_id_val);
			CALL  save_log(user_id_val, task_val, "none", newer_value_val);

									SELECT GROUP_CONCAT(orientation)INTO potential_orientation FROM inversions_in_species WHERE id = inv_id_val;


									IF info_val = 'standard' AND (potential_orientation IS NULL OR (current_inv_status_val NOT LIKE ('%inverted%'))) THEN
										SELECT ancestral_orientation INTO previous_value_val FROM inversions WHERE id = inv_id_val;
										UPDATE inversions SET ancestral_orientation = info_val WHERE id = inv_id_val;
										SELECT ancestral_orientation INTO newer_value_val FROM inversions WHERE id = inv_id_val;
										SET task_val = CONCAT('UPDATE ancestral_orientation of inv ',inv_id_val);
										CALL  save_log(user_id_val, task_val, previous_value_val, newer_value_val);

									ELSEIF info_val = 'inverted' AND (potential_orientation IS NULL OR (current_inv_status_val NOT LIKE ('%standard%'))) THEN
										SELECT ancestral_orientation INTO previous_value_val FROM inversions WHERE id = inv_id_val;
										UPDATE inversions SET ancestral_orientation = info_val WHERE id = inv_id_val;
										SELECT ancestral_orientation INTO newer_value_val FROM inversions WHERE id = inv_id_val;
										SET task_val = CONCAT('UPDATE ancestral_orientation of inv ',inv_id_val);
										CALL  save_log(user_id_val, task_val, previous_value_val, newer_value_val);
								
								
									END IF;





		END IF;
	
	

END CASE;	


	
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP PROCEDURE IF EXISTS `add_fosmid` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8 */ ;
/*!50003 SET character_set_results = utf8 */ ;
/*!50003 SET collation_connection  = utf8_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_AUTO_VALUE_ON_ZERO' */ ;
DELIMITER ;;
CREATE DEFINER=`invfest_admin`@`localhost` PROCEDURE `add_fosmid`(`fosmid_name_val` VARCHAR(255), `accession_val` VARCHAR(255), `type_val` VARCHAR(255), `study_method_val` VARCHAR(255), user_id_val INT)
    SQL SECURITY INVOKER
BEGIN
	 
DECLARE fosmid_id_val INT;
DECLARE task_val text;

INSERT INTO fosmids	(name, accession_code, type, study_method)  VALUES (fosmid_name_val, accession_val, type_val, study_method_val );	

SET fosmid_id_val = LAST_INSERT_ID() ;

	SET task_val = CONCAT('INSERT new sequence used for validation');
	CALL  save_log(user_id_val, task_val, "none", fosmid_id_val);  

END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP PROCEDURE IF EXISTS `add_fosmid_prediction` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8 */ ;
/*!50003 SET character_set_results = utf8 */ ;
/*!50003 SET collation_connection  = utf8_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_AUTO_VALUE_ON_ZERO' */ ;
DELIMITER ;;
CREATE DEFINER=`invfest_admin`@`localhost` PROCEDURE `add_fosmid_prediction`(IN `fosmid_id_val` INT, IN `prediction_id_val` INT, IN `research_val` VARCHAR(255), IN user_id_val INT)
    SQL SECURITY INVOKER
BEGIN
	 
DECLARE newer_value_val text;

INSERT INTO fosmids_predictions(fosmids_id, predictions_id, predictions_research_name)  VALUES (fosmid_id_val, prediction_id_val, research_val );	

SELECT LAST_INSERT_ID() INTO newer_value_val;

CALL  save_log(user_id_val, "INSERT INTO fosmids_predictions", "new entry", newer_value_val);


END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP PROCEDURE IF EXISTS `add_fosmid_validation` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8 */ ;
/*!50003 SET character_set_results = utf8 */ ;
/*!50003 SET collation_connection  = utf8_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = '' */ ;
DELIMITER ;;
CREATE DEFINER=`invfest_admin`@`localhost` PROCEDURE `add_fosmid_validation`(IN validation_id_val INT, IN inv_id_val INT, IN fosmid_name_val VARCHAR(255), IN research_val  VARCHAR(255), IN result_val  VARCHAR(255), IN comment_val  TEXT, IN user_id_val INT)
    SQL SECURITY INVOKER
BEGIN

 
  

	
  



	DECLARE previous_value_val text;
	DECLARE newer_value_val text;
	DECLARE task_val text;


 
 
 

 
 






	
	

	



















	
CALL add_fosmid (fosmid_name_val, NULL, NULL, NULL, user_id_val);

INSERT INTO fosmids_validation	(fosmids_name, validation_id, validation_research_name, inv_id, result, comments)
											VALUES (fosmid_name_val, validation_id_val,research_val, inv_id_val, result_val, comment_val);

SET newer_value_val = LAST_INSERT_ID() ;

	SET task_val = CONCAT('INSERT new fosmids_validation to inv ',inv_id_val);
	CALL  save_log(user_id_val, task_val, "none", newer_value_val);





									







		







											



									








					
										
	
	
	
			
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP PROCEDURE IF EXISTS `add_individual` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8 */ ;
/*!50003 SET character_set_results = utf8 */ ;
/*!50003 SET collation_connection  = utf8_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = '' */ ;
DELIMITER ;;
CREATE DEFINER=`invfest_admin`@`localhost` PROCEDURE `add_individual`(IN code_val VARCHAR(255), IN gender_val VARCHAR(255), IN population_val VARCHAR(255), IN region_val VARCHAR(255), IN family_val VARCHAR(255), IN panel_val VARCHAR(255), IN relationship_val VARCHAR(255), IN allele_level_val INT, IN other_code_val VARCHAR(255),  IN user_id_val INT)
    SQL SECURITY INVOKER
BEGIN
	
	DECLARE individual_id_val INT DEFAULT NULL;

	DECLARE previous_value_val text;
	DECLARE newer_value_val text;
	DECLARE task_val text;

IF NOT EXISTS (SELECT * FROM population WHERE name =  population_val) THEN
			INSERT INTO population (name, region) VALUES (population_val, region_val);
			SET newer_value_val = LAST_INSERT_ID() ;
			SET task_val = CONCAT('INSERT new population');
			CALL  save_log(user_id_val, task_val, "none", newer_value_val);
END IF;


SELECT id INTO individual_id_val
				FROM individuals
				WHERE  code =  code_val;

IF  individual_id_val IS NULL THEN
INSERT INTO individuals	(code,  gender, population, family, panel, relationship, allele_level, nickname, trio, trio_relationship)
											VALUES (code_val, gender_val, population_val, family_val, panel_val, relationship_val, allele_level_val, other_code_val, family_val, relationship_val );
			SET newer_value_val = LAST_INSERT_ID() ;
			SET task_val = CONCAT('INSERT new individual');
			CALL  save_log(user_id_val, task_val, "none", newer_value_val);
ELSEIF EXISTS (SELECT * FROM individuals WHERE code = code_val and ((gender IS NULL) OR (population IS NULL) OR (family IS NULL) OR (panel IS NULL) OR (relationship IS NULL) OR (trio IS NULL) OR (trio_relationship IS NULL))) THEN
	SELECT CONCAT('gender: ', gender, ' population: ', population, ' family: ', family, ' panel: ', panel, ' relationship: ', relationship) INTO previous_value_val FROM individuals WHERE code = code_val;
	UPDATE individuals SET gender = IFNULL(gender, gender_val), population = IFNULL(population, population_val), family = IFNULL(family, family_val), panel = IFNULL(panel, panel_val), relationship = IFNULL(relationship, relationship_val), trio = IFNULL(trio, family_val), trio_relationship = IFNULL(trio_relationship, relationship_val) WHERE code = code_val;
	SELECT CONCAT('gender: ', gender, ' population: ', population, ' family: ', family, ' panel: ', panel, ' relationship: ', relationship) INTO newer_value_val FROM individuals WHERE code = code_val;
	SET task_val = CONCAT('UPDATE fields in individual ', code_val);
	CALL  save_log(user_id_val, task_val, previous_value_val, newer_value_val);
END IF;

END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP PROCEDURE IF EXISTS `add_individual_genotipy` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8 */ ;
/*!50003 SET character_set_results = utf8 */ ;
/*!50003 SET collation_connection  = utf8_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_AUTO_VALUE_ON_ZERO' */ ;
DELIMITER ;;
CREATE DEFINER=`invfest_admin`@`localhost` PROCEDURE `add_individual_genotipy`(IN code_val VARCHAR(255), IN gender_val VARCHAR(255), IN population_val VARCHAR(255), IN region_val VARCHAR(255), IN family_val VARCHAR(255), IN panel_val VARCHAR(255), IN relationship_val VARCHAR(255), IN allele_level_val INT, IN `inv_id_val` INT(11), IN `genotype_val` VARCHAR(255), IN allele_comment_val VARCHAR(255), IN `valid_id_val` INT(11), IN user_id_val INT)
    SQL SECURITY INVOKER
BEGIN
	
DECLARE validation_research_name_val VARCHAR(255);



	DECLARE previous_value_val text;
	DECLARE newer_value_val text;
	DECLARE task_val text;


CALL  add_individual(code_val, gender_val, population_val, region_val, family_val, panel_val, relationship_val, allele_level_val, user_id_val);

	SELECT research_name INTO validation_research_name_val
				FROM validation
				WHERE  id =  valid_id_val;


IF NOT EXISTS (SELECT * FROM individuals_detection WHERE individuals_id = code_val AND  inversions_id = inv_id_val AND validation_id = valid_id_val) THEN
		IF EXISTS	(SELECT * FROM individuals_detection WHERE individuals_id = code_val AND inversions_id = inv_id_val AND genotype != genotype_val) THEN
			SET allele_comment_val = 'error more than one genotype inconsistent';
			
			SELECT allele_comment INTO previous_value_val FROM individuals_detection WHERE individuals_id = code_val AND inversions_id = inv_id_val;
			UPDATE individuals_detection SET allele_comment = CONCAT_WS('. ',allele_comment_val, allele_comment) WHERE individuals_id = code_val AND inversions_id = inv_id_val;
			SELECT allele_comment INTO newer_value_val FROM individuals_detection WHERE individuals_id = code_val AND inversions_id = inv_id_val;
			SET task_val = CONCAT('UPDATE allele_comment in individuals_detection individual ', code_val, ' of inv ',inv_id_val);
			CALL  save_log(user_id_val, task_val, previous_value_val, newer_value_val);
			INSERT INTO individuals_detection	(individuals_id, inversions_id, validation_id, validation_research_name, genotype, allele_comment)
							VALUES (code_val, inv_id_val, valid_id_val, validation_research_name_val, genotype_val, allele_comment_val);
			SET newer_value_val = LAST_INSERT_ID() ;
			SET task_val = CONCAT('INSERT new individuals_detection to inv ',inv_id_val);
			CALL  save_log(user_id_val, task_val, "none", newer_value_val);
		ELSE
			INSERT INTO individuals_detection	(individuals_id, inversions_id, validation_id, validation_research_name, genotype, allele_comment)
							VALUES (code_val, inv_id_val, valid_id_val, validation_research_name_val, genotype_val, allele_comment_val);
			SET newer_value_val = LAST_INSERT_ID() ;
			SET task_val = CONCAT('INSERT new individuals_detection to inv ',inv_id_val);
			CALL  save_log(user_id_val, task_val, "none", newer_value_val);
		END IF;
END IF;

END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP PROCEDURE IF EXISTS `add_individual_genotype` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8 */ ;
/*!50003 SET character_set_results = utf8 */ ;
/*!50003 SET collation_connection  = utf8_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = '' */ ;
DELIMITER ;;
CREATE DEFINER=`invfest_admin`@`localhost` PROCEDURE `add_individual_genotype`(IN code_val VARCHAR(255), IN gender_val VARCHAR(255), IN population_val VARCHAR(255), IN region_val VARCHAR(255), IN family_val VARCHAR(255), IN relationship_val VARCHAR(255), IN `genotype_val` VARCHAR(255), IN allele_comment_val VARCHAR(255), IN allele_level_val INT(11), IN panel_val VARCHAR(255), IN other_code_val VARCHAR(255), IN `inv_id_val` INT(11), IN `valid_id_val` INT(11), IN user_id_val INT(11))
    SQL SECURITY INVOKER
BEGIN
	
DECLARE validation_research_name_val VARCHAR(255);
DECLARE individual_id_val INT;


	DECLARE previous_value_val text;
	DECLARE newer_value_val text;
	DECLARE task_val text;

IF population_val = "" THEN SET population_val = "unknown"; END IF;
IF gender_val NOT IN ("Male", "Female") THEN SET gender_val = NULL ; END IF;

CALL  add_individual(code_val, gender_val, population_val, region_val, family_val, panel_val, relationship_val, allele_level_val, other_code_val, user_id_val);
SELECT id INTO individual_id_val
				FROM individuals
				WHERE  code =  code_val;

	SELECT research_name INTO validation_research_name_val
				FROM validation
				WHERE  id =  valid_id_val;



IF NOT EXISTS (SELECT * FROM individuals_detection WHERE individuals_id = code_val AND  inversions_id = inv_id_val AND validation_id = valid_id_val) THEN
		IF EXISTS	(SELECT * FROM individuals_detection WHERE individuals_id = code_val AND inversions_id = inv_id_val AND genotype != genotype_val) THEN
			SET allele_comment_val = 'error more than one genotype inconsistent';
			
			SELECT allele_comment INTO previous_value_val FROM individuals_detection WHERE individuals_id = code_val AND inversions_id = inv_id_val;
			UPDATE individuals_detection SET allele_comment = CONCAT_WS('. ',allele_comment_val, allele_comment) WHERE individuals_id = code_val AND inversions_id = inv_id_val;
			SELECT allele_comment INTO newer_value_val FROM individuals_detection WHERE individuals_id = code_val AND inversions_id = inv_id_val;
			SET task_val = CONCAT('UPDATE allele_comment in individuals_detection individual ', code_val, ' of inv ',inv_id_val);
			CALL  save_log(user_id_val, task_val, previous_value_val, newer_value_val);
			INSERT INTO individuals_detection	(individuals_id, inversions_id, validation_id, validation_research_name, genotype, allele_comment)
							VALUES (individual_id_val, inv_id_val, valid_id_val, validation_research_name_val, genotype_val, allele_comment_val);
			SET newer_value_val = LAST_INSERT_ID() ;
			SET task_val = CONCAT('INSERT new individuals_detection to inv ',inv_id_val);
			CALL  save_log(user_id_val, task_val, "none", newer_value_val);
		ELSE
			INSERT INTO individuals_detection	(individuals_id, inversions_id, validation_id, validation_research_name, genotype, allele_comment)
							VALUES (individual_id_val, inv_id_val, valid_id_val, validation_research_name_val, genotype_val, allele_comment_val);
			SET newer_value_val = LAST_INSERT_ID() ;
			SET task_val = CONCAT('INSERT new individuals_detection to inv ',inv_id_val);
			CALL  save_log(user_id_val, task_val, "none", newer_value_val);
		END IF;
END IF;

END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP PROCEDURE IF EXISTS `add_individual_prediction` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8 */ ;
/*!50003 SET character_set_results = utf8 */ ;
/*!50003 SET collation_connection  = utf8_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = '' */ ;
DELIMITER ;;
CREATE DEFINER=`invfest_admin`@`localhost` PROCEDURE `add_individual_prediction`(IN code_val VARCHAR(255), IN gender_val VARCHAR(255), IN population_val VARCHAR(255), IN region_val VARCHAR(255), IN family_val VARCHAR(255), IN relationship_val VARCHAR(255), IN `genotype_val` VARCHAR(255), IN allele_comment_val VARCHAR(255), IN allele_level_val INT(11), IN panel_val VARCHAR(255), IN other_code_val VARCHAR(255), IN `inv_id_val` INT(11), IN `pred_id_val` INT(11), IN user_id_val INT(11))
    SQL SECURITY INVOKER
BEGIN

# PREPARE 

DECLARE prediction_research_name_val VARCHAR(255);
DECLARE prediction_research_id_val VARCHAR(255);
DECLARE individual_id_val INT;
DECLARE test_val_det VARCHAR (255);
DECLARE test_val_res VARCHAR (255);

-- Declare variables used just for save the log
	DECLARE newer_value_val text;
	DECLARE task_val text;

IF population_val = "" THEN SET population_val = "unknown"; END IF;
IF gender_val NOT IN ("Male", "Female") THEN SET gender_val = NULL ; END IF;

# ADD/UPDATE INDIVIDUAL INFORMATION - if it does not exist

CALL  add_individual(code_val, gender_val, population_val, region_val, family_val, panel_val, relationship_val, allele_level_val, other_code_val, user_id_val);

# GET new/updated INDIVIDUAL ID by NAME

SELECT id  INTO individual_id_val
				FROM individuals
				WHERE  code =  code_val;

# GET STUDY NAME by PREDICTION ID
SELECT research_name INTO prediction_research_name_val
				FROM predictions
				WHERE  id =  pred_id_val;

# GET STUDY ID by PREDICTION ID
SELECT research_id INTO prediction_research_id_val
				FROM predictions
				WHERE  id =  pred_id_val;


SELECT EXISTS (SELECT 1 FROM individuals_detection WHERE individuals_id = individual_id_val AND inversions_id = inv_id_val AND prediction_id = pred_id_val) INTO test_val_det; 
			
IF test_val_det = 0 THEN
			INSERT INTO individuals_detection	(individuals_id, inversions_id, prediction_research_id, prediction_research_name, prediction_id)
							VALUES (individual_id_val, inv_id_val, prediction_research_id_val, prediction_research_name_val, pred_id_val );
			SET newer_value_val = LAST_INSERT_ID() ;
			SET task_val = CONCAT('INSERT new individuals_detection to inv ',inv_id_val);
			CALL  save_log(user_id_val, task_val, "none", newer_value_val);
END IF;

SELECT EXISTS (SELECT 1 FROM individual_research WHERE research_name =  prediction_research_name_val AND individual_id = individual_id_val) INTO test_val_res; 
			
IF test_val_res = 0 THEN
			INSERT INTO individual_research	(research_name ,individual_id)
							VALUES (prediction_research_name_val ,individual_id_val );
			SET newer_value_val = LAST_INSERT_ID() ;
			SET task_val = CONCAT('INSERT new individual_research to research ',prediction_research_name_val);
			CALL  save_log(user_id_val, task_val, "none", newer_value_val);
END IF;




END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP PROCEDURE IF EXISTS `add_info` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8 */ ;
/*!50003 SET character_set_results = utf8 */ ;
/*!50003 SET collation_connection  = utf8_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_AUTO_VALUE_ON_ZERO' */ ;
DELIMITER ;;
CREATE DEFINER=`invfest_admin`@`localhost` PROCEDURE `add_info`(IN `inv_name_val` VARCHAR(255), IN `chr_val` VARCHAR(255), IN `inv_BP1s_val` INT, IN `inv_BP1e_val` INT, IN `inv_BP2s_val` INT, IN `inv_BP2e_val` INT, IN `description_val` VARCHAR(255), IN `inv_status_val` VARCHAR(255), IN `inv_origin_val` VARCHAR(255), IN `comment_val` VARCHAR(255),  IN `inv_type_val` VARCHAR(255), IN user_id_val INT)
    SQL SECURITY INVOKER
BEGIN
	
 
  DECLARE inv_id_val INT;

 DECLARE same_BP_val  INT;


  
  

	



  
  
  




	
   


	SELECT id INTO inv_id_val
				FROM inversions
				WHERE  name =  inv_name_val;



IF (comment_val  = "") THEN	
SET comment_val = NULL;
END IF;
IF (inv_origin_val  = "") THEN	
SET inv_origin_val = NULL;
END IF;

UPDATE inversions SET status = inv_status_val, comment = comment_val, origin = inv_origin_val, type = inv_type_val WHERE id = inv_id_val;	






									
									
 
									









IF (description_val  = "Done") THEN	
SET description_val = NULL;
END IF;

	SET same_BP_val  = 0;	
										SELECT COUNT(*) INTO same_BP_val 
											FROM breakpoints
											WHERE  inv_id = inv_id_val AND chr = chr_val AND bp1_start = inv_BP1s_val AND bp1_end = inv_BP1e_val AND bp2_start = inv_BP2s_val AND bp2_end = inv_BP2e_val;

										IF same_BP_val = 0 THEN
											INSERT INTO breakpoints	(inv_id, chr, bp1_start, bp1_end, bp2_start, bp2_end, definition_method, description)
												VALUES (inv_id_val, chr_val, inv_BP1s_val, inv_BP1e_val,  inv_BP2s_val, inv_BP2e_val, "manual delimited", description_val );	
											UPDATE inversions SET range_start = inv_BP1s_val, range_end =  inv_BP2e_val, size = inv_BP2s_val -  inv_BP1e_val WHERE id = inv_id_val;	
										END IF;



END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP PROCEDURE IF EXISTS `add_IR` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8 */ ;
/*!50003 SET character_set_results = utf8 */ ;
/*!50003 SET collation_connection  = utf8_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = '' */ ;
DELIMITER ;;
CREATE DEFINER=`invfest_admin`@`localhost` PROCEDURE `add_IR`(IN `inversionid` int, IN `chrom1` VARCHAR(255), IN `position1start` int, IN `position1end` int, IN `size1` int, IN `chrom2` text, IN `position2start` int, IN `position2end` int, IN `size2` int, IN `identityy` float, IN `orientationn` TEXT)
BEGIN
INSERT INTO IR_in_BP (`inv_id`, `chrom`, `chromStart`,`chromEnd`,`size`,`otherChrom`,`otherStart`,`otherEnd`,`otherSize`,`fracMatch`, `strand`) VALUES(`inversionid`, `chrom1`, `position1start`, `position1end`, `size1`, `chrom2`,`position2start`,`position2end`,`size2`,`identityy`,`orientationn`);
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP PROCEDURE IF EXISTS `add_news` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8 */ ;
/*!50003 SET character_set_results = utf8 */ ;
/*!50003 SET collation_connection  = utf8_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = '' */ ;
DELIMITER ;;
CREATE DEFINER=`invfest_admin`@`localhost` PROCEDURE `add_news`(IN `news_title` text, IN `news_comment` text)
BEGIN
INSERT INTO News (`Title`, `Comment`, `Date`) VALUES(`news_title`,`news_comment`,now());
INSERT INTO `INVFEST-DB-PUBLIC`.News (`Title`, `Comment`, `Date`) VALUES(`news_title`,`news_comment`,now());
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP PROCEDURE IF EXISTS `add_phenotipic_effect` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8 */ ;
/*!50003 SET character_set_results = utf8 */ ;
/*!50003 SET collation_connection  = utf8_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = '' */ ;
DELIMITER ;;
CREATE DEFINER=`invfest_admin`@`localhost` PROCEDURE `add_phenotipic_effect`(IN inv_id_val INT, IN effect_val TEXT, IN source_val VARCHAR(255), IN user_id_val INT)
    SQL SECURITY INVOKER
BEGIN
	
	DECLARE newer_value_val INT;
	DECLARE task_val text;

	SET foreign_key_checks = 0;
	IF NOT EXISTS (SELECT * FROM phenotipic_effect WHERE effect = effect_val AND source = source_val AND inv_id = inv_id_val ) THEN 
		INSERT INTO phenotipic_effect(inv_id, effect, source) VALUES( inv_id_val, effect_val, source_val);
		SELECT LAST_INSERT_ID() INTO newer_value_val;
		SET task_val = CONCAT('INSERT new phenotipic_effects of inv ',inv_id_val);
		CALL  save_log(user_id_val, task_val, "none", newer_value_val);

	END IF;
	SET foreign_key_checks = 1;
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP PROCEDURE IF EXISTS `add_prediction` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8 */ ;
/*!50003 SET character_set_results = utf8 */ ;
/*!50003 SET collation_connection  = utf8_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = '' */ ;
DELIMITER ;;
CREATE DEFINER=`invfest_admin`@`localhost` PROCEDURE `add_prediction`(IN `newInv_chr_val` varchar(255),IN `newInv_bp1s_val` int,IN `newInv_bp1e_val` int,IN `newInv_bp2s_val` int,IN `newInv_bp2e_val` int,IN `newInv_studyName_val` varchar(255), IN user_id_val INT, OUT split_message VARCHAR(255))
    SQL SECURITY INVOKER
BEGIN

# DECLARE VARIABLES

	DECLARE newInv_RBP1s_val INT ; 
	DECLARE newInv_RBP1e_val INT ; 
	DECLARE newInv_RBP2s_val INT ; 
	DECLARE newInv_RBP2e_val INT ; 
	DECLARE inv_id_merged_val INT; 
	DECLARE prediction_error_val INT;  
	DECLARE name_val VARCHAR(255); 
	DECLARE num_rows_merged INT DEFAULT 0; 
	DECLARE newer_value_val VARCHAR(255); 
	DECLARE newInv_id_val INT; 
	DECLARE newInv_main_id_val INT DEFAULT 0; 
	DECLARE inv_evidences INT; 
	DECLARE task_val TEXT; 
	DECLARE loop_cntr_merged INT DEFAULT 0; 
	DECLARE previous_value_val VARCHAR(255); 
	DECLARE amount_merged_val INT; 
    DECLARE inv_list VARCHAR(255);
	DECLARE newinv INT;
    DECLARE amount_splits VARCHAR(255);
    
# DECLARE CURSOR  merge_inv_cur
#	DECLARE merge_inv_cur CURSOR FOR
#		SELECT  b.inv_id, COUNT(*) AS c
#		FROM inversions i 
#		INNER JOIN breakpoints b ON b.id =(SELECT id 
#				FROM breakpoints b2 WHERE b2.inv_id = i.id 
#				ORDER BY FIELD (b2.definition_method, 'manual curation', 'default informatic definition'),
#				b2.id DESC LIMIT 1) 
#		INNER JOIN predictions p ON p.inv_id = i.id
#		WHERE b.chr = newInv_chr_val 
#					AND(
#							( newInv_RBP1s_val BETWEEN b.bp1_start AND b.bp1_end ) 
#							OR 
#							( newInv_RBP1e_val BETWEEN b.bp1_start AND b.bp1_end )
#							OR
##							( (newInv_RBP1s_val <= b.bp1_start)  AND (newInv_RBP1e_val >=  b.bp1_end) )
	#						) 
	#				AND(
	#						(newInv_RBP2s_val BETWEEN b.bp2_start AND b.bp2_end) 
	#						OR 
	#						( newInv_RBP2e_val  BETWEEN b.bp2_start AND b.bp2_end )
	#						OR
	#						( (newInv_RBP2s_val <= b.bp2_start) AND (newInv_RBP2e_val  >= b.bp2_end))
	#					)	
	#	AND i.status NOT IN ('WITHDRAWN', 'withdrawn' , 'Withdrawn')
	#	GROUP BY b.inv_id;
#

# DECLARE CURSOR predictions_merged_cur

#	DECLARE predictions_merged_cur CURSOR FOR
#		SELECT COUNT(*) AS d
#		FROM predictions AS T
##		WHERE(
	#					( newInv_RBP1s_val BETWEEN T.RBP1s AND T.RBP1e) 
	#					OR 
	#					( newInv_RBP1e_val BETWEEN T.RBP1s AND T.RBP1e )
	#					OR
	#					( (newInv_RBP1s_val <= T.RBP1s)  AND (newInv_RBP1e_val >= T.RBP1e) )
	#					) 
	#			AND(
	#					( newInv_RBP2s_val BETWEEN T.RBP2s AND T.RBP2e) 
	#					OR 
	#					( newInv_RBP2e_val BETWEEN T.RBP2s AND T.RBP2e )
	#					OR
	#					( (newInv_RBP2s_val <= T.Rbp2s) AND (newInv_RBP2e_val >= T.RBP2e))
	#					)	
	#	AND newInv_chr_val = T.chr
	#	AND T.inv_id = inv_id_merged_val;

# START

	# Save confidence interval

		SELECT prediction_error INTO prediction_error_val
		FROM researchs
		WHERE  name =  newInv_studyName_val;
	
	# Apply confidence interval to coordinates
				
		IF (newInv_bp1e_val -  newInv_bp1s_val < prediction_error_val)  THEN
			SET newInv_RBP1s_val = newInv_bp1e_val  - prediction_error_val;
			SET newInv_RBP1e_val = newInv_bp1s_val + prediction_error_val;
		ELSE
			SET newInv_RBP1s_val = newInv_bp1s_val;
			SET newInv_RBP1e_val = newInv_bp1e_val;
		END IF ;
		IF (newInv_bp2e_val -  newInv_bp2s_val < prediction_error_val)  THEN
			SET newInv_RBP2s_val = newInv_bp2e_val  - prediction_error_val;
			SET newInv_RBP2e_val = newInv_bp2s_val + prediction_error_val;
		ELSE
			SET newInv_RBP2s_val = newInv_bp2s_val;
			SET newInv_RBP2e_val = newInv_bp2e_val;
		END IF ;

	# Serach for number of overlapping inversions
		
		#OPEN merge_inv_cur;
		#SELECT FOUND_ROWS() INTO num_rows_merged;
		#CLOSE merge_inv_cur;
        
        CALL find_bridge_prediction(NULL,newInv_chr_val, newInv_RBP1s_val, newInv_RBP1e_val, newInv_RBP2s_val, newInv_RBP2e_val, inv_list, num_rows_merged );
        
	# Set possible new inversion name

		SELECT CONCAT('HsInv', SUBSTRING(`name`, -4) +1) INTO  name_val
		FROM inversions
		WHERE `name` LIKE 'HsInv%' ORDER BY `name` DESC  LIMIT 1;

	# Set possible new research ID
	
		SELECT IF(MAX(research_id) IS NULL,1, MAX(research_id)+1) INTO newInv_id_val
		FROM predictions WHERE research_name = newInv_studyName_val;

# PROCESS INFORMATION

	# If it is a new inversion, create with the new name
    				
				IF num_rows_merged = 0 THEN 						
						INSERT INTO inversions	(name, chr, range_start, range_end, size, detected_amount, status) VALUES (name_val, newInv_chr_val, newInv_bp1s_val, newInv_bp2e_val, newInv_bp2s_val - newInv_bp1e_val, 1, 'ND');
						SELECT LAST_INSERT_ID() INTO newInv_main_id_val;
						SET task_val = CONCAT('INSERT 1 new inversions id');
						CALL  save_log(user_id_val, task_val, "none", newInv_main_id_val);
						INSERT INTO predictions	(inv_id, research_name, research_id, chr, BP1s, BP1e, BP2s, BP2e, RBP1s, RBP1e, RBP2s, RBP2e)
							VALUES (newInv_main_id_val, newInv_studyName_val, newInv_id_val, newInv_chr_val, newInv_bp1s_val, newInv_bp1e_val,  newInv_bp2s_val, newInv_bp2e_val, newInv_Rbp1s_val, newInv_Rbp1e_val,  newInv_Rbp2s_val, newInv_Rbp2e_val);
						SELECT LAST_INSERT_ID() INTO newer_value_val;
						SET task_val = CONCAT('INSERT 1 predictions to inv ',newInv_main_id_val);
						CALL  save_log(user_id_val, task_val, "none", newer_value_val);
						CALL update_BP(newInv_main_id_val,NULL, 'PREDICTION', user_id_val);
                        CALL revise_complexity(newInv_main_id_val, user_id_val );


	#  If there is only one inversion, the prediction is incorporated to the list
    
				ELSEIF num_rows_merged = 1 THEN 	
				# Take information from cursors

					#	FETCH  merge_inv_cur
					#		INTO	 inv_id_merged_val, inv_evidences;
		
				#		OPEN predictions_merged_cur;
				#			FETCH predictions_merged_cur
				#				INTO  amount_merged_val;
				#		CLOSE predictions_merged_cur;

		
				# Update existing inversion

						INSERT INTO predictions	(inv_id, research_name, research_id, chr, BP1s, BP1e, BP2s, BP2e, RBP1s, RBP1e, RBP2s, RBP2e)
							VALUES (inv_list, newInv_studyName_val, newInv_id_val, newInv_chr_val, newInv_bp1s_val, newInv_bp1e_val,  newInv_bp2s_val, newInv_bp2e_val, newInv_Rbp1s_val, newInv_Rbp1e_val,  newInv_Rbp2s_val, newInv_Rbp2e_val);	
						SELECT LAST_INSERT_ID() INTO newer_value_val;
						SET task_val = CONCAT('INSERT  prediction to inv ',inv_list);
						CALL  save_log(user_id_val, task_val, "none", newer_value_val);
												
						SELECT detected_amount INTO previous_value_val FROM inversions WHERE id = inv_list;
						UPDATE inversions SET detected_amount = detected_amount+1 WHERE id = inv_id_merged_val;
						SELECT detected_amount INTO newer_value_val FROM inversions WHERE id = inv_list;
						SET task_val = CONCAT('UPDATE detected_amount of inv ',inv_list);
						CALL  save_log(user_id_val, task_val, previous_value_val, newer_value_val);
						CALL update_BP(inv_list, NULL, 'PREDICTION', user_id_val);
						CALL revise_complexity(inv_list, user_id_val );

                        
	# VERSION 2: If there are more than 1 overlapping inversions, it is a BRIDGE prediction and joins all inversions
									
				ELSE 

#				SELECT CONCAT('') INTO inv_list;
#					 WHILE loop_cntr_merged < num_rows_merged DO 
					
					# Take information from cursors

				#		FETCH  merge_inv_cur
				#		INTO	 inv_id_merged_val, inv_evidences;
				#		
#
#						IF inv_list != '' THEN # It is not empty 
#							SELECT CONCAT(  inv_list, ',' , inv_id_merged_val ) INTO inv_list;
#						ELSE
#							SELECT CONCAT( inv_list, inv_id_merged_val) INTO inv_list;
#						END IF; 

					# Update control variable
#						SET loop_cntr_merged = loop_cntr_merged + 1; 
#					END WHILE ;
		
							
					# Now we have all the mergeable inversions

					CALL merge_inversions(inv_list, NULL, inv_list, NULL, NULL, NULL, NULL, NULL, NULL, inv_list, NULL, user_id_val, newinv);
				
					SELECT GROUP_CONCAT(i.name SEPARATOR ', ') 
						INTO split_message
						FROM inversion_history h
						INNER JOIN inversions i ON h.new_inv_id = i.id
						WHERE h.cause LIKE '%split%' AND 
							FIND_IN_SET(h.new_inv_id, inv_list)>0;

					
                    #SELECT CONCAT(amount_splits) INTO split_message;
					# Update inversion with the procedure

						INSERT INTO predictions	(inv_id, research_name, research_id, chr, BP1s, BP1e, BP2s, BP2e, RBP1s, RBP1e, RBP2s, RBP2e)
							VALUES (newinv, newInv_studyName_val, newInv_id_val, newInv_chr_val, newInv_bp1s_val, newInv_bp1e_val,  newInv_bp2s_val, newInv_bp2e_val, newInv_Rbp1s_val, newInv_Rbp1e_val,  newInv_Rbp2s_val, newInv_Rbp2e_val);	
						SELECT LAST_INSERT_ID() INTO newer_value_val;
						SET task_val = CONCAT('INSERT  prediction to inv ',newinv);
						CALL  save_log(user_id_val, task_val, "none", newer_value_val);
												
						SELECT detected_amount INTO previous_value_val FROM inversions WHERE id = newinv;
						UPDATE inversions SET detected_amount = detected_amount+1 WHERE id = newinv;
						SELECT detected_amount INTO newer_value_val FROM inversions WHERE id = newinv;
						SET task_val = CONCAT('UPDATE detected_amount of inv ',newinv);
						CALL  save_log(user_id_val, task_val, previous_value_val, newer_value_val);
						CALL update_BP(newinv, NULL,  'PREDICTION',user_id_val);	
						CALL revise_complexity(newinv, user_id_val );

					


				END IF;

	# Always revise complexity
  
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP PROCEDURE IF EXISTS `add_study` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8 */ ;
/*!50003 SET character_set_results = utf8 */ ;
/*!50003 SET collation_connection  = utf8_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_AUTO_VALUE_ON_ZERO' */ ;
DELIMITER ;;
CREATE DEFINER=`invfest_admin`@`localhost` PROCEDURE `add_study`(IN `study_name_val` varchar(255), IN `PMID_val` int(11), IN `year_val`int(11), IN `journal_val` varchar(255), IN `author_val` varchar(255), IN `pred_error_val` int(11), IN `aim_val` varchar(255), IN `method_val` varchar(255), IN `description_val` varchar(255), IN user_id_val INT)
    SQL SECURITY INVOKER
BEGIN


DECLARE previous_value_val text;
DECLARE newer_value_val text;
DECLARE task_val text;

DECLARE exist_method INT;

	IF aim_val = 'prediction' THEN 	
IF NOT EXISTS (SELECT * FROM researchs WHERE `name` = study_name_val) THEN
		INSERT INTO researchs	(`name`, aim, prediction_method,prediction_error, author, `year`, journal, pubMedID, description)
					VALUES (study_name_val, aim_val, method_val, pred_error_val, author_val, year_val, journal_val, PMID_val, description_val  );
		SET task_val = CONCAT('INSERT new researchs');
		CALL  save_log(user_id_val, task_val, "none", study_name_val);		
END IF;

	ELSE
		IF NOT EXISTS (SELECT * FROM researchs WHERE `name` = study_name_val) THEN
			INSERT INTO researchs	(`name`, aim, validation_method, author, `year`, journal, pubMedID, description )
					VALUES (study_name_val, aim_val, method_val, author_val, year_val, journal_val, PMID_val, description_val );
			SET task_val = CONCAT('INSERT new researchs');
			CALL  save_log(user_id_val, task_val, "none", study_name_val);		
		END IF;

	END IF;
	


	IF NOT EXISTS (SELECT * FROM methods WHERE `name` = method_val) THEN
		INSERT INTO methods (`name`, aim) VALUES (method_val, aim_val);
		SELECT LAST_INSERT_ID() INTO newer_value_val;
		SET task_val = CONCAT('INSERT new methods');
		CALL  save_log(user_id_val, task_val, "none", newer_value_val);	
	ELSE
	SELECT aim INTO previous_value_val FROM methods WHERE `name` = method_val;
		UPDATE methods SET aim = IF(aim = aim_val, aim,CONCAT_WS(',',aim, aim_val)) WHERE `name` = method_val;
		SELECT aim INTO newer_value_val FROM methods WHERE `name` = method_val;
		SET task_val = CONCAT('UPDATE ain of methods ', method_val);
		CALL  save_log(user_id_val, task_val, previous_value_val, newer_value_val);
	END IF;
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP PROCEDURE IF EXISTS `add_TE` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8 */ ;
/*!50003 SET character_set_results = utf8 */ ;
/*!50003 SET collation_connection  = utf8_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = '' */ ;
DELIMITER ;;
CREATE DEFINER=`invfest_admin`@`localhost` PROCEDURE `add_TE`(IN `inversionid` int, IN `subtype` text,IN `chrom1` VARCHAR(255), IN `position1start` int, IN `position1end` int, IN `size1` int, IN `chrom2` text, IN `position2start` int, IN `position2end` int, IN `size2` int, IN `identityy` float, IN `orientationn` TEXT)
BEGIN
INSERT INTO TE_in_BP (`inv_id`, `subtype`, `chrom`, `chromStart`,`chromEnd`,`size`,`otherChrom`,`otherStart`,`otherEnd`,`otherSize`,`fracMatch`, `strand`) VALUES(`inversionid`,`subtype`, `chrom1`, `position1start`, `position1end`, `size1`, `chrom2`,`position2start`,`position2end`,`size2`,`identityy`,`orientationn`);
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP PROCEDURE IF EXISTS `add_validation` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8 */ ;
/*!50003 SET character_set_results = utf8 */ ;
/*!50003 SET collation_connection  = utf8_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = '' */ ;
DELIMITER ;;
CREATE DEFINER=`invfest_admin`@`localhost` PROCEDURE `add_validation`(IN `inv_name_val` VARCHAR(255), IN `research_name_val` VARCHAR(255), IN `validation_val` VARCHAR(255), IN `valiadtion_method_val` VARCHAR(255), IN `PCRconditions_val` VARCHAR(255), IN `primer_val` VARCHAR(255),IN `validation_comment_val` TEXT ,IN `checked_val` VARCHAR(255), IN user_id_val INT)
    SQL SECURITY INVOKER
BEGIN

 
	DECLARE inv_id_val INT;
	DECLARE pred_id_val INT;
	DECLARE pred_research_name_val VARCHAR(255);

	DECLARE inversion_status_val  VARCHAR(255);
	DECLARE predition_status_val  VARCHAR(255);
	DECLARE current_inv_status_val  VARCHAR(255);
	DECLARE val_id  VARCHAR(255);

	DECLARE check_current_status VARCHAR(255);

	DECLARE no_more_rows_inversion BOOLEAN;
	DECLARE loop_cntr_inversion INT DEFAULT 0;
	DECLARE num_rows_inversion INT DEFAULT 0;

	DECLARE previous_value_val text;
	DECLARE newer_value_val text;
	DECLARE task_val text; 


  DECLARE prediction_cur CURSOR FOR
    SELECT
        research_id, research_name
    FROM predictions
		WHERE inv_id = inv_id_val;


	DECLARE CONTINUE HANDLER FOR NOT FOUND
    SET no_more_rows_inversion = TRUE;

	
    # Select the inversion ID

	SELECT id INTO inv_id_val
		FROM inversions
		WHERE  name =  inv_name_val;

	# Add validation to the database, and save log

	INSERT INTO validation	(research_name, inv_id, method, status, experimental_conditions, primers, comment, checked)
		VALUES (research_name_val, inv_id_val, valiadtion_method_val, validation_val, PCRconditions_val, primer_val, validation_comment_val, checked_val);	

	SET val_id = LAST_INSERT_ID() ;

	SET task_val = CONCAT('INSERT new validation to inv ',inv_id_val);
	CALL  save_log(user_id_val, task_val, "none", val_id);

	# Update the validation amount in the inversion
	SELECT validation_amount INTO previous_value_val 
		FROM inversions 
        WHERE id = inv_id_val;
	UPDATE inversions 
		SET validation_amount = validation_amount+1 
		WHERE id = inv_id_val;
	SELECT validation_amount INTO newer_value_val 
		FROM inversions 
		WHERE id = inv_id_val;
	SET task_val = CONCAT('UPDATE validation_amount of inv ',inv_id_val);
	CALL  save_log(user_id_val, task_val, previous_value_val, newer_value_val);

	# If the validation is not a Breakpoint curation
	IF validation_val != 'BP curation' THEN
									
		SET inversion_status_val = validation_val;
	
		# STEP 1: if our validation is 'checked', delete previous checks, force status and save log
		IF checked_val = 'yes' THEN
			# All the other validation will not be checked
			UPDATE validation SET checked = 'not' WHERE inv_id =  inv_id_val AND id != val_id_val;
			# Now we edit the inversion
            SELECT status INTO previous_value_val FROM inversions WHERE id = inv_id_val;
			UPDATE inversions SET status = inversion_status_val WHERE id = inv_id_val;
			SELECT status INTO newer_value_val FROM inversions WHERE id = inv_id_val;
			SET task_val = CONCAT('UPDATE status of inv ',inv_id_val);
			CALL save_log(user_id_val, task_val, previous_value_val, newer_value_val);
                        
   
		# STEP 2: if our validation is not 'checked':
		ELSE
		
			# STEP 3: search a forced status
			SELECT checked INTO check_current_status
				FROM validation
				WHERE inv_id = inv_id_val
				ORDER BY checked DESC LIMIT 1 ;

			# STEP 4: if there is not a forced (checked) status, compare and merge the information
			IF check_current_status != 'yes' THEN
             
				SELECT status INTO current_inv_status_val
					FROM inversions
                    WHERE id = inv_id_val;
   
				# STEP 5: if the validation has a specific status...
                IF inversion_status_val = 'TRUE' OR inversion_status_val = 'FALSE' THEN   
   
					# AND the current status is empty, set status and save log
                    IF current_inv_status_val IS NULL THEN
						SELECT status INTO previous_value_val FROM inversions WHERE id = inv_id_val;
                        UPDATE inversions SET status = inversion_status_val WHERE id = inv_id_val;
                        SELECT status INTO newer_value_val FROM inversions WHERE id = inv_id_val;
                        SET task_val = CONCAT('UPDATE status of inv ',inv_id_val);
                        CALL  save_log(user_id_val, task_val, previous_value_val, newer_value_val);
   
					# AND the current status is specific and contradictory, set 'Ambiguous' status and save log
                    ELSEIF (current_inv_status_val != inversion_status_val) AND ((current_inv_status_val = 'TRUE') OR (current_inv_status_val = 'FALSE') )THEN
						SELECT status INTO previous_value_val FROM inversions WHERE id = inv_id_val;
                        UPDATE inversions SET status = 'Ambiguous' WHERE id = inv_id_val;
                        SELECT status INTO newer_value_val FROM inversions WHERE id = inv_id_val;
                        SET task_val = CONCAT('UPDATE status of inv ',inv_id_val);
                        CALL  save_log(user_id_val, task_val, previous_value_val, newer_value_val);
             
					# ELSE we assume that the current status is specific and equal to the previous one
					END IF;
				END IF;
			END IF;
		END IF;
	
		# Update prediction status
        # This process has been generalized to avoid using cursors. 

		SET predition_status_val = CONCAT('on_', inversion_status_val);
		SELECT status INTO previous_value_val FROM predictions WHERE inv_id = inv_id_val LIMIT 1;
		UPDATE predictions SET status =  predition_status_val WHERE inv_id = inv_id_val;
		SET task_val = CONCAT('UPDATE status of predictions from inversion ', inv_id_val);
		CALL  save_log(user_id_val, task_val, previous_value_val, predition_status_val);		
		
	
    END IF;

END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP PROCEDURE IF EXISTS `calltest` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8 */ ;
/*!50003 SET character_set_results = utf8 */ ;
/*!50003 SET collation_connection  = utf8_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_AUTO_VALUE_ON_ZERO' */ ;
DELIMITER ;;
CREATE DEFINER=`invfest_admin`@`localhost` PROCEDURE `calltest`()
    SQL SECURITY INVOKER
BEGIN
	
DECLARE a INT;
DECLARE b INT;
DECLARE c INT;
DECLARE d INT;
CALL get_last_bp(1);

END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP PROCEDURE IF EXISTS `change_inversion_status` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8 */ ;
/*!50003 SET character_set_results = utf8 */ ;
/*!50003 SET collation_connection  = utf8_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_AUTO_VALUE_ON_ZERO' */ ;
DELIMITER ;;
CREATE DEFINER=`invfest_admin`@`localhost` PROCEDURE `change_inversion_status`(IN `inversion_id_val` int, IN `inversion_status_val` VARCHAR(255), IN user_id_val INT)
    SQL SECURITY INVOKER
BEGIN
	
 
  DECLARE inv_id_val INT;
  DECLARE research_name_val VARCHAR(255);
  DECLARE pred_id_val INT;

	DECLARE pred_status_val  VARCHAR(255);


  DECLARE no_more_rows_inversion BOOLEAN;
  DECLARE loop_cntr_inversion INT DEFAULT 0;
  DECLARE num_rows_inversion INT DEFAULT 0; 


  DECLARE inversion_cur CURSOR FOR
    SELECT
        inv_id, research_name, id
    FROM predictions
		WHERE inv_id = inversion_id_val;


	DECLARE CONTINUE HANDLER FOR NOT FOUND
    SET no_more_rows_inversion = TRUE;


	UPDATE inversions SET status = inversion_status_val WHERE id = inversion_id_val;	

	SET pred_status_val = concat("on_", inversion_status_val);	

OPEN inversion_cur;
	SELECT FOUND_ROWS() INTO num_rows_inversion;
WHILE loop_cntr_inversion < num_rows_inversion DO 

									FETCH  inversion_cur
									INTO	inv_id_val, research_name_val, pred_id_val;
 
									UPDATE predictions SET status = pred_status_val WHERE inv_id = inversion_id_val;	

SET loop_cntr_inversion = loop_cntr_inversion + 1; 									
END WHILE ;



END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP PROCEDURE IF EXISTS `checking_prediction_in_rmsk` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8 */ ;
/*!50003 SET character_set_results = utf8 */ ;
/*!50003 SET collation_connection  = utf8_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_AUTO_VALUE_ON_ZERO' */ ;
DELIMITER ;;
CREATE DEFINER=`invfest_admin`@`localhost` PROCEDURE `checking_prediction_in_rmsk`(IN `pred_id_val` INT, IN `research_name_val` VARCHAR(255), IN user_id_val INT)
    SQL SECURITY INVOKER
BEGIN

UPDATE predictions SET checking = 'TRUE', status = 'FALSE', comments = 'prediction within repeats '  WHERE research_id = pred_id_val  and research_name = research_name_val;




END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP PROCEDURE IF EXISTS `data_add_fosmid_validation` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8 */ ;
/*!50003 SET character_set_results = utf8 */ ;
/*!50003 SET collation_connection  = utf8_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_AUTO_VALUE_ON_ZERO' */ ;
DELIMITER ;;
CREATE DEFINER=`invfest_admin`@`localhost` PROCEDURE `data_add_fosmid_validation`(IN fosmid_id_val INT, IN pred_id_val INT, IN research_val  VARCHAR(255), IN result_val  VARCHAR(255), IN comment_val  VARCHAR(255), IN chr_val  VARCHAR(255), IN bp1s_val INT, IN bp1e_val INT, IN bp2s_val INT, IN bp2e_val INT, IN user_id_val INT)
    SQL SECURITY INVOKER
BEGIN

 
  DECLARE inv_id_val INT;

	DECLARE inversion_status_val  VARCHAR(255) DEFAULT 'possible_TRUE';
  DECLARE predition_status_val  VARCHAR(255) DEFAULT 'possible_TRUE';


  DECLARE no_more_rows_inversion BOOLEAN;
  DECLARE loop_cntr_inversion INT DEFAULT 0;
  DECLARE num_rows_inversion INT DEFAULT 0; 

  DECLARE no_more_rows_inv BOOLEAN;
  DECLARE loop_cntr_inv INT DEFAULT 0;
  DECLARE num_rows_inv INT DEFAULT 0; 

	DECLARE current_inv_status_val  VARCHAR(255);
	DECLARE current_pred_status_val  VARCHAR(255);


	
	
	DECLARE valiadation_method_val  VARCHAR(255);
	DECLARE validation_id_val INT;
	DECLARE prediction_id_val INT;
 	DECLARE pred_research_name_val VARCHAR(255);




DECLARE prediction_cur CURSOR FOR
    SELECT
        research_id, research_name
    FROM predictions
		WHERE inv_id = inv_id_val;


DECLARE inv_cur CURSOR FOR
			SELECT inv_id 
				FROM predictions
				WHERE research_id = pred_id_val AND research_name =  research_val;


	DECLARE CONTINUE HANDLER FOR NOT FOUND
    SET no_more_rows_inversion = TRUE;
		SET no_more_rows_inv = TRUE;


	
OPEN inv_cur;
				SELECT FOUND_ROWS() INTO num_rows_inv;
WHILE loop_cntr_inv < num_rows_inv DO 

FETCH  inv_cur
INTO inv_id_val;

IF result_val = 'Validated' THEN
SET inversion_status_val  = "TRUE";
ELSE 
SET inversion_status_val  = "FALSE";

END IF;

SET valiadation_method_val  = 'complete sequenced Fosmid analisis';

INSERT INTO validation(research_name, inv_id, method, status)
											VALUES (research_val, inv_id_val, valiadation_method_val, inversion_status_val);

SELECT LAST_INSERT_ID() INTO validation_id_val;

INSERT INTO fosmids_validation	(fosmids_id, validation_id, validation_research_name, inv_id, result, comments)
											VALUES (fosmid_id_val, validation_id_val,research_val, inv_id_val, result_val, comment_val);

									SELECT status INTO current_inv_status_val
										FROM inversions
										WHERE id = inv_id_val;
									
									IF current_inv_status_val IS NULL THEN
											UPDATE inversions SET status = inversion_status_val WHERE id = inv_id_val;
									ELSEIF (current_inv_status_val != inversion_status_val) AND (current_inv_status_val = 'TRUE' OR current_inv_status_val = 'possible_TRUE' )THEN
											UPDATE inversions SET status = 'ambiguous validation results' WHERE id = inv_id_val;
									ELSE 
											UPDATE inversions SET status = inversion_status_val WHERE id = inv_id_val;
									END IF;
IF bp1s_val != 0 THEN
INSERT INTO breakpoints	(inv_id, chr, bp1_start, bp1_end, bp2_start, bp2_end, definition_method)
											VALUES (inv_id_val, chr_val, bp1s_val, bp1e_val,  bp2s_val, bp2e_val, "complete sequenced Fosmid analisis");	
										UPDATE inversions SET range_start = bp1s_val, range_end =  bp2e_val, size = bp2s_val -  bp1e_val WHERE id = inv_id_val;	
END IF;
		
	UPDATE predictions SET checking = 'TRUE' WHERE research_id = pred_id_val AND research_name =  research_val;	
			SET predition_status_val = CONCAT('on_', inversion_status_val);
	
			OPEN prediction_cur;
				SELECT FOUND_ROWS() INTO num_rows_inversion;
			WHILE loop_cntr_inversion < num_rows_inversion DO 

											FETCH  prediction_cur
											INTO	prediction_id_val, pred_research_name_val;
											
										SELECT status INTO current_pred_status_val
										FROM predictions
										WHERE research_id = prediction_id_val AND research_name =  pred_research_name_val AND inv_id = inv_id_val;	
									
									IF current_pred_status_val IS NULL THEN
									UPDATE predictions SET status =  predition_status_val WHERE research_id = prediction_id_val AND research_name =  pred_research_name_val AND inv_id = inv_id_val;
									ELSEIF (current_inv_status_val != inversion_status_val) AND (current_inv_status_val = 'TRUE' OR current_inv_status_val = 'possible_TRUE' )THEN
											UPDATE predictions SET status = 'ambiguous validation results' WHERE research_id = prediction_id_val AND research_name =  pred_research_name_val AND inv_id = inv_id_val;
									ELSE 
												UPDATE predictions SET status =  predition_status_val WHERE research_id = prediction_id_val AND research_name =  pred_research_name_val AND inv_id = inv_id_val;
									END IF;

					
										
			SET loop_cntr_inversion = loop_cntr_inversion + 1; 									
			END WHILE ;
			CLOSE prediction_cur;
			

SET loop_cntr_inv = loop_cntr_inv + 1; 									
END WHILE ;

END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP PROCEDURE IF EXISTS `delete_news` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8 */ ;
/*!50003 SET character_set_results = utf8 */ ;
/*!50003 SET collation_connection  = utf8_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = '' */ ;
DELIMITER ;;
CREATE DEFINER=`invfest_admin`@`localhost` PROCEDURE `delete_news`(IN `news_id` float)
BEGIN
DELETE FROM News where id = `news_id`;
DELETE FROM `INVFEST-DB-PUBLIC`.News where id = `news_id`;
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP PROCEDURE IF EXISTS `delete_validation` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8 */ ;
/*!50003 SET character_set_results = utf8 */ ;
/*!50003 SET collation_connection  = utf8_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = '' */ ;
DELIMITER ;;
CREATE DEFINER=`invfest_admin`@`localhost` PROCEDURE `delete_validation`(IN `val_id_val` INT, IN user_id_val INT)
    SQL SECURITY INVOKER
BEGIN

 
  DECLARE inv_id_val INT;

  DECLARE inversion_status_val  VARCHAR(255);
  DECLARE predition_status_val  VARCHAR(255);

  DECLARE check_current_status VARCHAR(255);

  DECLARE previous_value_val text;
  DECLARE newer_value_val text;
  DECLARE task_val text; 


 SELECT inv_id INTO inv_id_val 
				FROM validation
				WHERE  id =  val_id_val;

 DELETE FROM validation WHERE id=val_id_val and inv_id=inv_id_val ;


  SET task_val = CONCAT('DELETE validation with id ',val_id_val, ' from inv ', inv_id_val );
	CALL  save_log(user_id_val, task_val,val_id_val, "none" ); 


  # STEP 1: search a forced status
        SELECT checked INTO check_current_status
            FROM validation
            WHERE inv_id = inv_id_val
            ORDER BY checked DESC
            LIMIT 1 ;


# STEP 2: if there is not a forced (checked) status, compare and merge the information
       IF check_current_status != 'yes' THEN
             
			 # Two specific, contradictory statuses
				IF (SELECT EXISTS(SELECT status FROM validation WHERE inv_id = inv_id_val AND status = 'FALSE')) = 1 AND 
				   (SELECT EXISTS(SELECT status FROM validation WHERE inv_id = inv_id_val AND status = 'TRUE')) = 1 THEN
								
					          SELECT status INTO previous_value_val FROM inversions WHERE id = inv_id_val;
                              UPDATE inversions SET status = 'Ambiguous' WHERE id = inv_id_val;
							  SELECT status INTO newer_value_val FROM inversions WHERE id = inv_id_val;
                              SET task_val = CONCAT('UPDATE status of inv ',inv_id_val);
                              CALL  save_log(user_id_val, task_val, previous_value_val, newer_value_val);
			 # One specific status:TRUE
				ELSEIF  (SELECT EXISTS(SELECT status FROM validation WHERE inv_id = inv_id_val AND status = 'TRUE')) = 1 THEN
							  SELECT status INTO previous_value_val FROM inversions WHERE id = inv_id_val;
							  UPDATE inversions SET status = 'TRUE' WHERE id = inv_id_val;
                              SELECT status INTO newer_value_val FROM inversions WHERE id = inv_id_val;
                              SET task_val = CONCAT('UPDATE status of inv ',inv_id_val);
							  CALL  save_log(user_id_val, task_val, previous_value_val, newer_value_val);
			# One specific status: FALSE

				ELSEIF (SELECT EXISTS(SELECT status FROM validation WHERE inv_id = inv_id_val AND status = 'FALSE')) = 1 THEN
							  SELECT status INTO previous_value_val FROM inversions WHERE id = inv_id_val;
                         	  UPDATE inversions SET status = 'FALSE' WHERE id = inv_id_val;
                              SELECT status INTO newer_value_val FROM inversions WHERE id = inv_id_val;
                              SET task_val = CONCAT('UPDATE status of inv ',inv_id_val);
							  CALL  save_log(user_id_val, task_val, previous_value_val, newer_value_val);
			# All non specific results
				ELSE 
								
							  SELECT status INTO previous_value_val FROM inversions WHERE id = inv_id_val;
                              UPDATE inversions SET status = 'ND' WHERE id = inv_id_val;
                              SELECT status INTO newer_value_val FROM inversions WHERE id = inv_id_val;
                              SET task_val = CONCAT('UPDATE status of inv ',inv_id_val);
							  CALL  save_log(user_id_val, task_val, previous_value_val, newer_value_val);
                END IF;	

# STEP 2: If there is a checked status
		ELSEIF check_current_status = 'yes' THEN
			SELECT status INTO inversion_status_val FROM validation WHERE inv_id = inv_id_val ORDER BY checked DESC LIMIT 1 ;
		#	UPDATE validation SET checked = '' WHERE inv_id =  inv_id_val AND id != val_id_val;
			SELECT status INTO previous_value_val FROM inversions WHERE id = inv_id_val;
			UPDATE inversions SET status = inversion_status_val WHERE id = inv_id_val;
			SELECT status INTO newer_value_val FROM inversions WHERE id = inv_id_val;
			SET task_val = CONCAT('UPDATE status of inv ',inv_id_val);
			CALL  save_log(user_id_val, task_val, previous_value_val, newer_value_val);
   
        END IF;
	
	# This process has been generalized to avoid using cursors. 

	SET predition_status_val = CONCAT('on_', inversion_status_val);
	SELECT status INTO previous_value_val FROM predictions WHERE inv_id = inv_id_val LIMIT 1;
	UPDATE predictions SET status =  predition_status_val WHERE inv_id = inv_id_val;
	SET task_val = CONCAT('UPDATE status of predictions from inversion ', inv_id_val);
	CALL  save_log(user_id_val, task_val, previous_value_val, predition_status_val);		
						


END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP PROCEDURE IF EXISTS `find_bridge_prediction` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8 */ ;
/*!50003 SET character_set_results = utf8 */ ;
/*!50003 SET collation_connection  = utf8_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = '' */ ;
DELIMITER ;;
CREATE DEFINER=`invfest_admin`@`localhost` PROCEDURE `find_bridge_prediction`(IN inversion_id INT, IN chrval VARCHAR(255), IN bp1s INT, IN bp1e INT, IN bp2s INT, IN bp2e INT, OUT inv_list VARCHAR(255), OUT amount INT )
BEGIN
 

CALL get_last_bp(NULL); 

IF inversion_id IS NULL THEN
	SELECT GROUP_CONCAT(b.inv_id),   count(*)
	INTO inv_list, amount
	FROM  last_bp_output b 
    INNER JOIN inversions i ON b.inv_id = i.id
        WHERE  b.chr = chrval
					AND(
							( bp1s BETWEEN b.bp1_start AND b.bp1_end ) 
							OR 
							(  bp1e BETWEEN b.bp1_start AND b.bp1_end )
							OR
							( ( bp1s <= b.bp1_start)  AND (bp1e >=  b.bp1_end) )
							) 
					AND(
							(bp2s BETWEEN b.bp2_start AND b.bp2_end) 
							OR 
							( bp2e   BETWEEN b.bp2_start AND b.bp2_end )
							OR
							( (bp2s  <= b.bp2_start) AND (bp2e     >= b.bp2_end))
						)	
                        
        	AND i.status  NOT LIKE  'withdrawn'
		;
ELSE
 
 # A kind of less greedy procedure to reduce the runtime
	DROP TEMPORARY TABLE IF EXISTS suitable_inversions_a;
	DROP TEMPORARY TABLE IF EXISTS suitable_inversions_b;

	# Inversions that can undergo merges	
	CREATE TEMPORARY TABLE suitable_inversions_a
	SELECT p.inv_id, p.chr, 
			MIN(IF(  p.research_name = "Martinez-Fundichely et al. 2013", p.BP1s,  p.RBP1s) ) as bp1s, 
			MAX(IF(  research_name = "Martinez-Fundichely et al. 2013", BP1e,  RBP1e) ) as bp1e, 
			MIN(IF(  research_name = "Martinez-Fundichely et al. 2013", BP2s,  RBP2s) ) as bp2s, 
			MAX(IF(  p.research_name = "Martinez-Fundichely et al. 2013", p.BP2e,  p.RBP2e)) as bp2e
			FROM predictions p INNER JOIN inversions i ON i.id = p.inv_id
			WHERE i.status NOT LIKE 'withdrawn' AND p.inv_id != inversion_id GROUP BY p.inv_id;
				
	# Our inversion of interest
	CREATE TEMPORARY TABLE suitable_inversions_b
	SELECT p.inv_id, p.chr, 
			MIN(IF(  p.research_name = "Martinez-Fundichely et al. 2013", p.BP1s,  p.RBP1s) ) as bp1s, 
			MIN(IF(  research_name = "Martinez-Fundichely et al. 2013", BP1e,  RBP1e)) as bp1e, 
			MIN(IF(  research_name = "Martinez-Fundichely et al. 2013", BP2s,  RBP2s) )as bp2s, 
			MAX(IF(  p.research_name = "Martinez-Fundichely et al. 2013", p.BP2e,  p.RBP2e)) as bp2e
			FROM predictions p INNER JOIN inversions i ON i.id = p.inv_id
			WHERE  p.inv_id = inversion_id;
            
	# List of inversions that in the best of cases could overlap with our inversion of interest
	SELECT GROUP_CONCAT(a.inv_id)
	INTO inv_list
		FROM suitable_inversions_a  a
		JOIN suitable_inversions_b  b
		WHERE a.chr = b.chr
						AND(
								( a.bp1s BETWEEN b.bp1s AND b.bp1e ) 
								OR 
								(  a.bp1e BETWEEN b.bp1s AND b.bp1e )
								OR
								( ( a.bp1s <= b.bp1s)  AND (a.bp1e >=  b.bp1e) )
								) 
						AND(
								(a.bp2s BETWEEN b.bp2s AND b.bp2e) 
								OR 
								( a.bp2e   BETWEEN b.bp2s AND b.bp2e )
								OR
								( (a.bp2s  <= b.bp2s) AND (a.bp2e     >= a.bp2e))
							)	
			
		;
	 
     # Find actual inversion breakpoints
	CALL get_last_bp(NULL);  
      
	# Select only the breakpoints that in the best of the cases could merge with our inversion
    DROP TEMPORARY TABLE IF EXISTS suitable_breakpoints;
    CREATE TEMPORARY TABLE suitable_breakpoints 
    SELECT inv_id, chr, bp1_start, bp1_end, bp2_start, bp2_end FROM last_bp_output WHERE  FIND_IN_SET(inv_id, inv_list) ;

	# Find those inversions that actually have a bridge prediction connecting them to our inversion of interest
	SELECT GROUP_CONCAT(b.inv_id),   count(*)
	INTO inv_list, amount
	FROM  (  SELECT inv_id, chr,
		IF(  research_name = "Martinez-Fundichely et al. 2013", BP1s,  RBP1s) as bp1s, 
		IF(  research_name = "Martinez-Fundichely et al. 2013", BP1e,  RBP1e) as bp1e, 
		IF(  research_name = "Martinez-Fundichely et al. 2013", BP2s,  RBP2s) as bp2s, 
		IF(  research_name = "Martinez-Fundichely et al. 2013", BP2e,  RBP2e) as bp2e
	#	INTO chromosome_val, RBP1s_val, RB1e_val, RBP2s_val, RBP2e_val
		FROM predictions
		WHERE  inv_id = @inversion_id) as p  
        INNER JOIN  suitable_breakpoints b
        WHERE p.chr = b.chr
					AND(
							( p.bp1s BETWEEN b.bp1_start AND b.bp1_end ) 
							OR 
							(  p.bp1e BETWEEN b.bp1_start AND b.bp1_end )
							OR
							( ( p.bp1s <= b.bp1_start)  AND (p.bp1e >=  b.bp1_end) )
							) 
					AND(
							(p.bp2s BETWEEN b.bp2_start AND b.bp2_end) 
							OR 
							( p.bp2e   BETWEEN b.bp2_start AND b.bp2_end )
							OR
							( (p.bp2s  <= b.bp2_start) AND (p.bp2e     >= b.bp2_end))
						)	
    
		;
        
 
        
END IF;

END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP PROCEDURE IF EXISTS `get_inv_gene_realtion` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8 */ ;
/*!50003 SET character_set_results = utf8 */ ;
/*!50003 SET collation_connection  = utf8_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = '' */ ;
DELIMITER ;;
CREATE DEFINER=`invfest_admin`@`localhost` PROCEDURE `get_inv_gene_realtion`(IN `bp_id_val` VARCHAR(255))
    SQL SECURITY INVOKER
BEGIN

 
DECLARE inv_id_value INT;
DECLARE bp_id_value INT;
DECLARE idHsRefSeqGenes_val INT;
DECLARE gene_symbol_val, typeRow_val VARCHAR(255);
DECLARE typeWithinGene_val VARCHAR(255);
DECLARE typeRow2_val VARCHAR(255);


  DECLARE no_more_rows_inversion BOOLEAN;
  DECLARE loop_cntr_inversion INT DEFAULT 0;
  DECLARE num_rows_inversion INT DEFAULT 0; 


	

	
 

  DECLARE relation_cur CURSOR FOR
    SELECT typeRowQuery.inv_id, typeRowQuery.id, typeRowQuery.idHsRefSeqGenes, typeRowQuery.symbol, typeRow, typeRow2,
CASE 
     WHEN (typeRow='breakWithinGene' AND withinExons=0 AND withinIntrons=0) THEN 'amongDiffRegions' 
     WHEN (typeRow='breakWithinGene' AND withinExons>0) THEN 'withinExon'
     WHEN (typeRow='breakWithinGene' AND withinIntrons>0) THEN 'withinIntron'
     ELSE 'NA' END AS typeWithinGene
FROM (
SELECT  basicQuery.inv_id, basicQuery.id, basicQuery.idHsRefSeqGenes, symbol, BP1_complete, BP1_partial, BP2_complete, BP2_partial, 
(BP1_complete+BP1_partial+BP2_complete+BP2_partial) AS sumRow,
CASE 
     WHEN (BP1_complete+BP1_partial+BP2_complete+BP2_partial)<=1 THEN 'intergenic'
     WHEN (BP1_complete+BP1_partial+BP2_complete+BP2_partial)>=3 THEN 'breakWithinGene'
     WHEN (BP1_complete+BP1_partial+BP2_complete+BP2_partial)=2 AND (BP1_complete+BP1_partial)=2 THEN 'break1gene'
     WHEN (BP1_complete+BP1_partial+BP2_complete+BP2_partial)=2 AND (BP2_complete+BP2_partial)=2 THEN 'break1gene'
     WHEN (BP1_complete+BP1_partial+BP2_complete+BP2_partial)=2 AND (BP1_complete+BP2_complete)=0 THEN 'intergenic'
     ELSE '???' END AS typeRow,
CASE 
     WHEN (BP1_complete+BP1_partial+BP2_complete+BP2_partial)=2 AND (BP1_complete+BP1_partial)=2 THEN 'BP1'
     WHEN (BP1_complete+BP1_partial+BP2_complete+BP2_partial)=2 AND (BP2_complete+BP2_partial)=2 THEN 'BP2'
     ELSE '' END AS typeRow2,
COUNT(ge.idHsRefSeqGenes) AS withinExons,
COUNT(gi.idHsRefSeqGenes) AS withinIntrons     
FROM (
SELECT i.inv_id, i.id, g.idHsRefSeqGenes, g.symbol, i.bp1_end, i.bp2_start,
IF ( (
(i.bp1_start BETWEEN g.txStart AND g.txEnd) AND
(i.bp1_end BETWEEN g.txStart AND g.txEnd)
), 1, 0) AS BP1_complete,
IF ( (
(i.bp1_start BETWEEN g.txStart AND g.txEnd) OR
(i.bp1_end BETWEEN g.txStart AND g.txEnd)
), 1, 0) AS BP1_partial,
IF ( (
(i.bp2_start BETWEEN g.txStart AND g.txEnd) AND
(i.bp2_end BETWEEN g.txStart AND g.txEnd)
), 1, 0) AS BP2_complete,
IF ( (
(i.bp2_start BETWEEN g.txStart AND g.txEnd) OR
(i.bp2_end BETWEEN g.txStart AND g.txEnd)
), 1, 0) AS BP2_partial
FROM breakpoints i, HsRefSeqGenes g 
WHERE i.id = bp_id_val AND g.chr = i.chr AND ((
(i.bp1_start BETWEEN g.txStart AND g.txEnd) OR
(i.bp1_end BETWEEN g.txStart AND g.txEnd)
) OR (
(i.bp2_start BETWEEN g.txStart AND g.txEnd) OR
(i.bp2_end BETWEEN g.txStart AND g.txEnd)
))
) AS basicQuery
	LEFT JOIN HsRefSeqGenes_exons ge ON (basicQuery.idHsRefSeqGenes = ge.idHsRefSeqGenes AND 
		(basicQuery.bp1_end >= ge.exonStart AND basicQuery.bp2_start <= ge.exonEnd))
	LEFT JOIN HsRefSeqGenes_introns gi ON (basicQuery.idHsRefSeqGenes = gi.idHsRefSeqGenes AND
		(basicQuery.bp1_end >= gi.intronStart AND basicQuery.bp2_start <= gi.intronEnd))
GROUP BY basicQuery.idHsRefSeqGenes
) AS typeRowQuery;   


	DECLARE CONTINUE HANDLER FOR NOT FOUND
    SET no_more_rows_inversion = TRUE;


DROP TEMPORARY TABLE IF EXISTS temp_table;
CREATE TEMPORARY TABLE temp_table (inv_id INT (11), bp_id INT(11), idHsRefSeqGenes INT(11) , symbol VARCHAR(255), typeRow VARCHAR(255), typeRow2 VARCHAR(255),typeWithinGene VARCHAR(255));


OPEN relation_cur;
	SELECT FOUND_ROWS() INTO num_rows_inversion;
WHILE loop_cntr_inversion < num_rows_inversion DO 

									FETCH  relation_cur
									INTO	inv_id_value, bp_id_value, idHsRefSeqGenes_val, gene_symbol_val, typeRow_val, typeRow2_val, typeWithinGene_val;

								INSERT INTO temp_table  (inv_id, bp_id, idHsRefSeqGenes, symbol, typeRow, typeRow2, typeWithinGene)
																					VALUES(inv_id_value, bp_id_value, idHsRefSeqGenes_val, gene_symbol_val, typeRow_val, typeRow2_val,typeWithinGene_val);



								INSERT INTO genomic_effect (inv_id, bp_id, gene_id, gene_relation)
														    values (inv_id_value, bp_id_value, idHsRefSeqGenes_val, CONCAT_WS( ', ', typeRow_val, typeWithinGene_val)) ; 
									
SET loop_cntr_inversion = loop_cntr_inversion + 1; 									
END WHILE ;

UPDATE breakpoints SET genomic_effect = (  
SELECT 
CASE 
     WHEN typeRow='break1gene' AND COUNT(DISTINCT typeRow2) =2 THEN 'break2genes'
     WHEN typeRow='break1gene' AND COUNT(DISTINCT typeRow2) =1 THEN 'break1gene'
     WHEN (typeRow='breakWithinGene' AND typeWithinGene='amongDiffRegions') THEN 'breakWithinGene_amongDiffRegions'
     WHEN (typeRow='breakWithinGene' AND typeWithinGene='withinExon') THEN 'breakWithinGene_withinExon'
     WHEN (typeRow='breakWithinGene' AND typeWithinGene='withinIntron') THEN 'breakWithinGene_withinIntron'
     ELSE 'intergenic' END AS finalDecision
FROM temp_table 
GROUP BY typeRow ORDER BY FIELD(typeRow, 'break1gene', 'breakWithinGene', 'intergenic'), 
																														FIELD(typeWithinGene, 'amongDiffRegions', 'withinExon', 'withinIntron', 'NA') 
LIMIT 1
)WHERE id = bp_id_val;

UPDATE breakpoints SET genomic_effect = 'intergenic' WHERE id = bp_id_val AND genomic_effect IS NULL;


DELETE FROM genomic_effect WHERE gene_relation = 'intergenic, NA' AND bp_id = bp_id_val;


DROP TEMPORARY TABLE temp_table;
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP PROCEDURE IF EXISTS `get_last_bp` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8 */ ;
/*!50003 SET character_set_results = utf8 */ ;
/*!50003 SET collation_connection  = utf8_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = '' */ ;
DELIMITER ;;
CREATE DEFINER=`invfest_admin`@`localhost` PROCEDURE `get_last_bp`( IN current_assembly VARCHAR(255) )
    SQL SECURITY INVOKER
BEGIN

IF current_assembly IS NULL THEN 

# Select last assembly

         SELECT Column_Default
 			INTO current_assembly
 			FROM Information_Schema.Columns
 			WHERE 
 				Table_Schema = 'INVFEST-DB'    AND
 				Table_Name = 'breakpoints'  AND 
 				Column_Name = 'assembly';        
END IF;

DROP TEMPORARY TABLE IF EXISTS last_bp_output;
CREATE TEMPORARY TABLE last_bp_output SELECT i.id AS inv_id, b.id, b.chr, b.bp1_start, b.bp1_end, b.bp2_start, b.bp2_end, b.definition_method
FROM inversions i
INNER JOIN breakpoints b ON b.id = (
    SELECT id FROM breakpoints b2
    WHERE b2.inv_id = i.id AND b2.assembly = current_assembly
    ORDER BY FIELD(b2.definition_method, 'manual curation', 'default informatic definition', NULL), b2.`date` DESC
    LIMIT 1
)  ;

# SELECT CONCAT('Your data is stored in last_bp_output');	
	
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP PROCEDURE IF EXISTS `get_SD_in_BP` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8 */ ;
/*!50003 SET character_set_results = utf8 */ ;
/*!50003 SET collation_connection  = utf8_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = '' */ ;
DELIMITER ;;
CREATE DEFINER=`invfest_admin`@`localhost` PROCEDURE `get_SD_in_BP`(IN `bp_id_val` VARCHAR(255))
    SQL SECURITY INVOKER
BEGIN

 
  
  DECLARE SD_id_val INT;
	DECLARE SD_in_BP1_val INT;
  DECLARE SD_in_BP2_val INT;
	DECLARE strand_val  VARCHAR(255);
	DECLARE change_strand_val VARCHAR(255) DEFAULT '+';
 



  DECLARE no_more_rows_inversion BOOLEAN;
  DECLARE loop_cntr_inversion INT DEFAULT 0;
  DECLARE num_rows_inversion INT DEFAULT 0; 


	

	


 DECLARE prediction_cur CURSOR FOR
 SELECT sd.id, sd.strand, 
								IF ( (( sd.chromStart BETWEEN  bp.bp1_start  AND bp.bp1_end )  OR  ( sd.chromEnd BETWEEN  bp.bp1_start  AND bp.bp1_end  ) OR ( (sd.chromStart <=  bp.bp1_start )  AND (sd.chromEnd >= bp.bp1_end ) ) ),	1 , 0 ) AS SD_in_BP1,
								IF ( (( sd.otherStart BETWEEN bp.bp2_start  AND bp.bp2_end )  OR  ( sd.otherEnd BETWEEN bp.bp2_start  AND bp.bp2_end  ) 	OR  ( ( sd.otherStart <= bp.bp2_start ) AND (sd.otherEnd >= bp.bp2_end )	) ), 1, 0) AS SD_in_BP2 

FROM seg_dups AS sd , breakpoints AS bp
WHERE bp.id = bp_id_val AND sd.chrom = bp.chr AND sd.chrom = sd.otherChrom 
							AND(   
													(
														(sd.chromStart >= bp.bp1_start AND sd.chromStart <= bp.bp1_end) OR 
														(sd.chromEnd >=  bp.bp1_start AND sd.chromEnd <=   bp.bp1_end) OR 
														(sd.chromStart <= bp.bp1_start AND sd.chromEnd >= bp.bp1_end) 
													)
													OR
													(
														(sd.otherStart >= bp.bp2_start AND sd.otherStart <= bp.bp2_end) OR 
														(sd.otherEnd >=  bp.bp2_start AND sd.otherEnd <=  bp.bp2_end) OR 
														(sd.otherStart <= bp.bp2_start AND sd.otherEnd >= bp.bp2_end) 
													)
												)
ORDER BY SD_in_BP1+SD_in_BP2;


	DECLARE CONTINUE HANDLER FOR NOT FOUND
    SET no_more_rows_inversion = TRUE;



DROP TEMPORARY TABLE IF EXISTS temp_table;
CREATE TEMPORARY TABLE temp_table (SD_id INT(11), strand VARCHAR(255), SD_in_BP1 INT(11), SD_in_BP2 INT(255));



OPEN prediction_cur;
	SELECT FOUND_ROWS() INTO num_rows_inversion;
WHILE loop_cntr_inversion < num_rows_inversion DO 

									FETCH  prediction_cur
									INTO	SD_id_val, strand_val,  SD_in_BP1_val, SD_in_BP2_val ; 

INSERT INTO temp_table  (SD_id, strand,  SD_in_BP1, SD_in_BP2)
								VALUES(SD_id_val, strand_val,  SD_in_BP1_val, SD_in_BP2_val);
 
INSERT INTO SD_in_BP (BP_id, SD_id, type)
values (`bp_id_val` , `SD_id_val`, 
(
CASE 
WHEN SD_in_BP1_val+SD_in_BP2_val = 2 THEN CONCAT('2BPs_pair' , strand_val) 
WHEN SD_in_BP1_val = 1 AND SD_in_BP2_val = 0 THEN 'BP1'
ELSE 'BP2'
END 
) 
) ;

 										
SET loop_cntr_inversion = loop_cntr_inversion + 1; 									
END WHILE ;

    SELECT strand FROM temp_table WHERE SD_in_BP1+ SD_in_BP2 = 2 ORDER BY FIELD(strand, '-', '+' ) LIMIT 1 INTO  change_strand_val;

    UPDATE breakpoints SET SD_relation = ( 
    SELECT
    CASE
    WHEN count(*) = 3 THEN CONCAT('2BPs_pair' , change_strand_val)
    WHEN count(*) = 1 AND tt.SD_in_BP1 = 1 AND tt.SD_in_BP2 = 0  THEN 'BP1'
    WHEN count(*) = 1 AND tt.SD_in_BP2 = 1 AND tt.SD_in_BP1 = 0 THEN 'BP2'
    WHEN count(*) = 1 AND tt.SD_in_BP2 = 1 AND tt.SD_in_BP1 = 1 THEN CONCAT('2BPs_pair' , change_strand_val)
    WHEN count(*) = 2 AND (SUM(tt.SD_in_BP1) = 2 OR SUM(tt.SD_in_BP2) = 2) THEN CONCAT('2BPs_pair' , change_strand_val)
    WHEN count(*) = 2 AND (SUM(tt.SD_in_BP1) = 1 AND SUM(tt.SD_in_BP2) = 1) THEN '2BPs_nopair'
    ELSE '0BPs'
    END
    FROM (SELECT * FROM temp_table GROUP BY SD_in_BP1, SD_in_BP2) AS tt
    ) WHERE id = bp_id_val;

    DROP TEMPORARY TABLE temp_table;

    END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP PROCEDURE IF EXISTS `inv_iterator` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8 */ ;
/*!50003 SET character_set_results = utf8 */ ;
/*!50003 SET collation_connection  = utf8_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = '' */ ;
DELIMITER ;;
CREATE DEFINER=`invfest_admin`@`localhost` PROCEDURE `inv_iterator`()
BEGIN

DECLARE inversion_id_val INT;

  DECLARE no_more_rows_inversion1 BOOLEAN;
  DECLARE loop_cntr_inversion1 INT DEFAULT 0;
  DECLARE num_rows_inversion1 INT DEFAULT 0;

	DECLARE inversion_cur1 CURSOR FOR
    SELECT id FROM inversions;


OPEN inversion_cur1;
	SELECT FOUND_ROWS() INTO num_rows_inversion1;
	WHILE loop_cntr_inversion1 < num_rows_inversion1 DO 
		FETCH  inversion_cur1	INTO	inversion_id_val;
			
			CALL update_BP_public_info(inversion_id_val);

		SET loop_cntr_inversion1 = loop_cntr_inversion1 + 1; 
	END WHILE ;
CLOSE inversion_cur1;

END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP PROCEDURE IF EXISTS `merge_inversions` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8 */ ;
/*!50003 SET character_set_results = utf8 */ ;
/*!50003 SET collation_connection  = utf8_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = '' */ ;
DELIMITER ;;
CREATE DEFINER=`invfest_admin`@`localhost` PROCEDURE `merge_inversions`( old_inv_list VARCHAR(255), optional_id INT,old_mech_list VARCHAR(255),old_bp1s_val INT(11), old_bp1e_val INT(11), old_bp2s_val INT(11), old_bp2e_val INT(11),old_evo_id INT, old_func_id INT, old_comm_list VARCHAR(255), old_status_id VARCHAR(255), user_id_val INT, OUT working_inv_id INT )
    SQL SECURITY INVOKER
BEGIN

# DECLARE VARIABLES

# Basics
DECLARE previous_value_val text;
DECLARE newer_value_val text;
DECLARE task_val text;

DECLARE new_rstart INT  DEFAULT 0;
DECLARE	new_rend INT  DEFAULT 0;
DECLARE new_size INT  DEFAULT 0;

DECLARE new_Inv_name VARCHAR(255);
DECLARE cause_action text;

DECLARE loop_cntr INT DEFAULT 0;
DECLARE num_rows INT DEFAULT 0;
		
DECLARE inv_id_val INT;
DECLARE research_name_val VARCHAR(255);
DECLARE research_id_val INT;
DECLARE chr_val VARCHAR(255); 
DECLARE BP1s_val INT;
DECLARE BP1e_val INT;
DECLARE BP2s_val INT;
DECLARE BP2e_val INT;
DECLARE RBP1s_val INT;
DECLARE RBP1e_val INT;
DECLARE RBP2s_val INT;
DECLARE RBP2e_val INT;
DECLARE pstatus_val VARCHAR(255);
DECLARE paccuracy_val VARCHAR(255);
DECLARE pchecking_val VARCHAR(255);
DECLARE pcomments_val TEXT;
DECLARE psupport_val INT;
DECLARE psupport_bp2_val INT;
DECLARE pscore1_val FLOAT;
DECLARE pscore2_val FLOAT;
DECLARE prediction_name_val VARCHAR(255);

DECLARE new_predi_id_val INT;

DECLARE pred_individuals_id_val INT;
DECLARE pred_research_name_val VARCHAR(255);
DECLARE pred_research_id_val INT;

DECLARE amount_merged_pred INT; 
DECLARE comp INT; 

DECLARE vali_id_val VARCHAR(255);
DECLARE method_val VARCHAR(255);
DECLARE status_val VARCHAR(255);
DECLARE experimental_conditions_val VARCHAR(255);
DECLARE primers_val VARCHAR(255);
DECLARE comment_val TEXT ;
DECLARE checked_val VARCHAR(255);
DECLARE  bp_id_val INT; 

DECLARE new_vali_id_val INT;

DECLARE vali_individuals_id_val INT;
DECLARE vali_research_name_val VARCHAR(255);
DECLARE vali_genotype_val  VARCHAR(255);
DECLARE vali_allele_comment_val VARCHAR(255);

DECLARE old_status_val VARCHAR(255);
DECLARE new_status_val VARCHAR(255);
DECLARE new_amount_merged_pred INT;
DECLARE new_amount_merged_val INT;
DECLARE inv_id_merged_val INT; 

DECLARE history_cause_val VARCHAR(500);

#Extras
DECLARE new_mech_val TEXT;
DECLARE new_ancor_val VARCHAR(255); 
DECLARE new_age_val INT(11); 
DECLARE new_evor_val VARCHAR(255);

# DECLARE CURSORS

# HISTORY_CUR <-select events in history similar to this
#	DECLARE history_cur CURSOR for	
#		SELECT previous_inv_id 
#			FROM inversion_history
#			WHERE (FIND_IN_SET(previous_inv_id, old_inv_list) > 0 OR previous_inv_id = optional_id) 
#			AND (FIND_IN_SET(new_inv_id, old_inv_list) > 0 OR new_inv_id = optional_id);

# PREDI CUR <- select all the predictions from the involved inversions, without repetition (group by)
	DECLARE predi_cur CURSOR FOR
     SELECT A.inv_id,A.research_name, A.research_id, A.chr, A.BP1s, A.BP1e, A.BP2s, A.BP2e, A.RBP1s, A.RBP1e, A.RBP2s, A.RBP2e, A.status, 
					A.accuracy, A.checking, A.comments, A.support, A.support_bp2, A.score1, A.score2, A.prediction_name 
		FROM (
			SELECT  inv_id, research_name, research_id, chr, BP1s, BP1e, BP2s, BP2e, RBP1s, RBP1e, RBP2s, RBP2e, status, 
					accuracy, checking, comments, support, support_bp2, score1, score2, prediction_name
				FROM predictions 
				WHERE  FIND_IN_SET(inv_id, old_inv_list) >0 
				GROUP BY research_name, research_id) A
		LEFT JOIN (
			SELECT research_name, research_id 
				FROM predictions  
                WHERE  FIND_IN_SET(inv_id, optional_id) >0 
                GROUP BY research_name, research_id ) B
		ON B.research_id = A.research_id AND B.research_name = B.research_name
		WHERE B.research_id IS NULL;
                

# EVO_CUR<-select orientation in other species
	DECLARE evo_cur CURSOR FOR
		SELECT species_id, orientation, method, source, num_ind, result_value 
			FROM inversions_in_species 
			WHERE FIND_IN_SET(inversions_id, old_evo_list);


# PREDICTIONS MERGED CUR <- find all the predictions in the new inversion overlapping with the query prediction
	DECLARE predictions_merged_cur CURSOR FOR
		SELECT COUNT(*) AS d
		FROM predictions AS T
		WHERE(
						( RBP1s_val BETWEEN T.RBP1s AND T.RBP1e) 
						OR 
						( RBP1e_val BETWEEN T.RBP1s AND T.RBP1e )
						OR
						( (RBP1s_val <= T.RBP1s)  AND (RBP1e_val >= T.RBP1e) )
						) 
				AND(
						( RBP2s_val BETWEEN T.RBP2s AND T.RBP2e) 
						OR 
						( RBP2e_val BETWEEN T.RBP2s AND T.RBP2e )
						OR
						( (RBP2s_val <= T.Rbp2s) AND (RBP2e_val >= T.RBP2e))
						)	
		AND chr_val = T.chr
		AND T.inv_id = working_inv_id;

# VALI CUR <- select  all the validations from the involved inversions, without repetition (group by)
	DECLARE vali_cur CURSOR FOR
		SELECT DISTINCT id, research_name, inv_id, method, status, experimental_conditions, primers, comment, checked, bp_id
		FROM `validation`
		WHERE FIND_IN_SET(inv_id, old_inv_list) > 0; 

					
# FOREIGN KEY

SET FOREIGN_KEY_CHECKS=0;
        
  ### BEGIN
  
 # SELECT CONCAT(old_inv_list, optional_id);
  

# START
IF old_inv_list != 'NA'  THEN  # Basic checkpoint
	
	# An inversion that has been a source of merge cannot be merged again (it should have disappeared)
	SELECT t.count, GROUP_CONCAT(t.new_inv_id) 
		INTO num_rows, new_Inv_name
        FROM(
		SELECT COUNT(previous_inv_id) as count , new_inv_id
			FROM inversion_history WHERE cause LIKE '%merge%' AND FIND_IN_SET(previous_inv_id, old_inv_list) >0
			GROUP BY new_inv_id) t	;
           
    # An optional ID does not disappear, so we can add information to it, but it has not to be withdrawn
   
	SELECT COUNT(status) 
		INTO num_rows 
        FROM inversions 
        where id = optional_id AND
				FIND_IN_SET(status,'WITHDRAWN,withdrawn,Withdrawn');
                
	# Restart loop
   
	IF num_rows > 0 THEN
		SELECT CONCAT('This procedure was already done: ', new_Inv_name);		
        SET working_inv_id = NULL;
	ELSE
	
		SET num_rows = 0;
		
		# 1. CREATE INVERSION
        
        # No existent inversion
        # Create inversion
		IF optional_id IS NULL THEN 
			# New empty inversion
			INSERT INTO inversions	(chr, range_start, range_end, size, detected_amount)
			#  This variables are to have something... they will be correctly estimated with update_BP
            SELECT chr, MIN(range_start), MAX(range_end), MIN(size), MAX(detected_amount) 
				FROM inversions WHERE FIND_IN_SET(id, old_inv_list) > 0;
			# Store the ID
			SELECT LAST_INSERT_ID() INTO working_inv_id; 
			# SAVE LOG
			SET task_val = CONCAT('INSERT new inversions ');
			CALL  save_log(user_id_val, task_val, "none", working_inv_id);
			# Set NAME
			SELECT CONCAT('HsInv', SUBSTRING(`name`, -4) +1) 
				INTO  new_Inv_name
				FROM inversions 
                WHERE `name` LIKE 'HsInv%' 
                ORDER BY `name` DESC  
                LIMIT 1;
			UPDATE inversions 
				SET `name` =  new_Inv_name  
                WHERE id = working_inv_id 
                LIMIT 1;
			# SET cause action
			SET cause_action = ' merge into ';
			
		# Exitent inversion
        # Assign inversion
		ELSE 
			
			SELECT optional_id INTO working_inv_id;
			# Store name
			SELECT name 
				INTO  new_Inv_name	
                FROM inversions 
                WHERE id = working_inv_id ;
			
			SET cause_action = ' incorporated information to ';


		END IF;
	
		# Basic checkpoint to asess that the previous step is complete.
		IF working_inv_id != 0 THEN 	
        
        # 2. TAKE PREDICTIONS
		# Insert all involved predictions into the working inversion. For this we need the PREDI CUR
		OPEN  predi_cur;
			SELECT FOUND_ROWS() INTO num_rows;
           
            # For each prediction in the inversions that don't give name
			WHILE loop_cntr < num_rows DO 
				# Apply prediction to inversion
				FETCH  predi_cur INTO inv_id_val, research_name_val, research_id_val, chr_val, BP1s_val, BP1e_val, BP2s_val, BP2e_val, RBP1s_val, RBP1e_val, RBP2s_val, RBP2e_val, pstatus_val, paccuracy_val, pchecking_val, pcomments_val, psupport_val, psupport_bp2_val, pscore1_val, pscore2_val, prediction_name_val;
                INSERT INTO predictions (inv_id, research_name, research_id, chr, BP1s, BP1e, BP2s, BP2e, RBP1s, RBP1e, RBP2s, RBP2e, status, accuracy, checking, comments, support, support_bp2, score1, score2, prediction_name) 
					VALUE (working_inv_id, research_name_val, research_id_val, chr_val, BP1s_val, BP1e_val, BP2s_val, BP2e_val, RBP1s_val, RBP1e_val, RBP2s_val, RBP2e_val, pstatus_val, paccuracy_val, pchecking_val, pcomments_val, psupport_val, psupport_bp2_val, pscore1_val, pscore2_val, prediction_name_val);
				# Save the prediction id
				
                SELECT LAST_INSERT_ID() INTO new_predi_id_val; 
				# Insert into individuals detection
				INSERT INTO individuals_detection (individuals_id, inversions_id, prediction_research_id, prediction_research_name, prediction_id)
					SELECT ind_det.individuals_id, working_inv_id, ind_det.prediction_research_id, ind_det.prediction_research_name, new_predi_id_val  
						FROM individuals_detection AS ind_det
						WHERE ind_det.inversions_id = inv_id_val 
							AND ind_det.prediction_research_id = research_id_val 
							AND ind_det.prediction_research_name = research_name_val;
											
				SET loop_cntr = loop_cntr + 1; 
			END WHILE; 
		CLOSE predi_cur;

		# Restart loop
		SET loop_cntr = 0;
		SET num_rows = 0;
		
		# 3. TAKE VALIDATIONS
		# Insert all involved validations into the working inversion. For this we need the VALI CUR
		OPEN  vali_cur;
			SELECT FOUND_ROWS() INTO num_rows;
			# For each validation in the inversions that don't give name
			WHILE loop_cntr < num_rows DO 
				# Apply validation to invesion
               
				FETCH  vali_cur INTO vali_id_val, research_name_val , inv_id_val, method_val, status_val, experimental_conditions_val, primers_val, comment_val, checked_val, bp_id_val;
                INSERT INTO `validation`	(research_name, inv_id, method, status, experimental_conditions, primers, comment, checked, bp_id) 
					VALUE (research_name_val , working_inv_id, method_val, status_val, experimental_conditions_val, primers_val, comment_val, checked_val, bp_id_val);
				# Save the validation id
				SELECT LAST_INSERT_ID() INTO new_vali_id_val; 

				# Insert into individuals detection	
				INSERT INTO individuals_detection (individuals_id, inversions_id, validation_id, validation_research_name, genotype, allele_comment)
					SELECT ind_det.individuals_id, working_inv_id, new_vali_id_val, ind_det.validation_research_name, ind_det.genotype, ind_det.allele_comment  
						FROM individuals_detection AS ind_det 
						WHERE ind_det.inversions_id = inv_id_val
							AND ind_det.validation_id = vali_id_val 
							AND ind_det.validation_research_name = research_name_val;
									
				SET loop_cntr = loop_cntr + 1; 
			END WHILE; 
		CLOSE vali_cur;

		# Restart loop
		SET loop_cntr = 0;
		SET num_rows = 0;

		# 4. BREAKPOINT SETTING
		IF (old_bp1s_val IS NOT NULL AND old_bp1e_val IS NOT NULL AND old_bp2s_val IS NOT NULL AND old_bp2e_val IS NOT NULL)
		  AND  (old_bp1s_val <= old_bp1s_val AND old_bp1e_val <= old_bp2s_val AND old_bp2s_val <= old_bp2e_val)  THEN
			SELECT chr INTO chr_val FROM inversions WHERE id = working_inv_id;
			CALL add_BP('NA', working_inv_id,chr_val, old_bp1s_val,old_bp1e_val,old_bp2s_val,old_bp2e_val,'InvFEST curation', user_id_val);
		ELSE 
		    CALL update_BP(working_inv_id, '', 'MERGE',user_id_val);

		END IF;
       
	# 4.5. UPDATE COMPLEXITY
		# Compare all inserted predictions with themselves. For this we need the revise_complexity procedure
        
        CALL revise_complexity(working_inv_id , user_id_val); 
		
	# 5. ESTIMATE STATUS AND DETECTED AMOUNTS
       
			IF old_status_id IS NOT NULL THEN
				SELECT status INTO old_status_val FROM inversions WHERE id = working_inv_id;
				SET new_status_val = old_status_id;
								
		ELSE
           # in the case of not optional_id, the latter where is null
				SELECT GROUP_CONCAT(status) INTO old_status_val 
					FROM inversions 
					WHERE FIND_IN_SET(id, old_inv_list) > 0 OR id = working_inv_id; 
				
               IF old_status_val LIKE ('%TRUE%') AND old_status_val NOT LIKE('%FALSE%') THEN SET new_status_val = 'TRUE';
				ELSEIF old_status_val LIKE ('%FALSE%') AND old_status_val NOT LIKE ('%TRUE%') THEN SET new_status_val = 'FALSE';		
				ELSEIF old_status_val LIKE ('%FALSE%') AND old_status_val LIKE ('%TRUE%') THEN SET new_status_val = 'Ambiguous';
				ELSE SET new_status_val = 'ND';
				END IF;	

       END IF;
	
       	SELECT DISTINCT count(id) INTO new_amount_merged_pred FROM predictions WHERE inv_id = working_inv_id;
			SELECT DISTINCT count(id) INTO new_amount_merged_val FROM `validation` WHERE inv_id = working_inv_id;
			
			UPDATE inversions 
				SET status = new_status_val, detected_amount = new_amount_merged_pred, validation_amount = new_amount_merged_val
				WHERE id = working_inv_id LIMIT 1 ;

			SET task_val = CONCAT('UPDATE status of inv ', working_inv_id);
			CALL  save_log(user_id_val, task_val, old_status_val, new_status_val);
			
			SELECT GROUP_CONCAT(status SEPARATOR '; ') INTO previous_value_val FROM inversions WHERE FIND_IN_SET(id, old_inv_list) > 0;
				
			UPDATE inversions 
				SET status =  "withdrawn" 
				WHERE FIND_IN_SET(id, old_inv_list) > 0 LIMIT 1000;

			SELECT GROUP_CONCAT(status SEPARATOR '; ') INTO newer_value_val FROM inversions WHERE FIND_IN_SET(id, old_inv_list) > 0;
				
			SET task_val = CONCAT('UPDATE status of inv ', old_inv_list);
			CALL  save_log(user_id_val, task_val, previous_value_val, newer_value_val);
			

		# 6. SAVE HISTORY SENTENCE
			
			SELECT GROUP_CONCAT( CONCAT('<a href="report.php?q=', CAST(id AS CHAR), '">', `name`,'</a>') SEPARATOR ' and ') INTO history_cause_val 
				FROM inversions WHERE FIND_IN_SET(id, old_inv_list) > 0;

			SET history_cause_val = CONCAT( history_cause_val,cause_action,'<a href="report.php?q=', CAST(working_inv_id AS CHAR), '">', new_Inv_name, '</a>');
			
			
			INSERT INTO inversion_history (previous_inv_id, new_inv_id, cause)
				SELECT id, working_inv_id, history_cause_val FROM inversions WHERE FIND_IN_SET(id, old_inv_list) > 0;

		# THE BASIC STEPS ARE DONE. NOW THE OPTIONAL STEPS START
	# 8. MECHANISM
			IF old_mech_list IS NOT NULL THEN
				SELECT  GROUP_CONCAT(DISTINCT(origin))  
					INTO new_mech_val		
					FROM inversions 
					WHERE FIND_IN_SET(id, old_mech_list) > 0
						OR id = optional_id;
				UPDATE inversions SET origin = new_mech_val WHERE id=working_inv_id;
			END IF;
	
		# 9. EVOLUTIONARY HISTORY
			IF old_evo_id IS NOT NULL THEN
				IF old_evo_id != working_inv_id THEN # If the inversion that aports the evolutionary info is the same as the name, it is in the tables already. If it is different, the information in the name inversion will be preserved anyway.
					# Inversion table
					SELECT ancestral_orientation, age, evo_origin 
						INTO new_ancor_val, new_age_val, new_evor_val
						FROM inversions 
						WHERE id=old_evo_id;
					UPDATE inversions 
						SET  ancestral_orientation =new_ancor_val,  age = new_age_val, evo_origin = new_evor_val 
						WHERE id=working_inv_id;
					# Orientation in other species
					INSERT INTO inversions_in_species (species_id, inversions_id, orientation, method, source, num_ind, result_value) 
						SELECT invsp.species_id, working_inv_id ,invsp.orientation, invsp.method, invsp.source, invsp.num_ind, invsp.result_value
							FROM inversions_in_species AS invsp
							WHERE invsp.inversions_id = old_evo_id;
				
					# Inversion origin
					INSERT INTO inv_origin (inv_id,origin, method, source)
						SELECT  working_inv_id, invor.origin, invor.method, invor.source 
							FROM inv_origin AS invor
								WHERE invor.inv_id = old_evo_id;
				
					#Inversion age
					INSERT INTO inv_age (inv_id,age, method, source)
						SELECT working_inv_id , GROUP_CONCAT(a.age ORDER BY a.age ASC SEPARATOR '-') AS age, GROUP_CONCAT(DISTINCT a.method) AS method, a.source#, r.year, r.pubMedID 
							FROM inv_age a LEFT JOIN researchs r ON r.name=a.source 
							WHERE a.inv_id=old_evo_id 
							GROUP BY a.source 
							ORDER BY r.year, a.source;
				END IF;
			END IF;
		# 10. FUNCTIONAL CONSEQUENCES
			IF old_func_id IS NOT NULL THEN
				IF old_func_id != working_inv_id THEN 
					INSERT INTO genomic_effect (inv_id, gene_id, bp_id, gene_relation, comment, source, functional_effect, functional_consequence)
						SELECT working_inv_id, ge.gene_id, ge.bp_id, ge.gene_relation, ge.comment, ge.source, ge.functional_effect, ge.functional_consequence  FROM genomic_effect ge
							WHERE inv_id = old_func_id OR inv_id = working_inv_id
								AND id=(SELECT MAX(g2.id) 
									FROM genomic_effect g2 
									WHERE ge.gene_id = g2.gene_id);	 
				END IF;
			END IF;
	# 11. COMMENTS
			IF old_comm_list IS NOT NULL then	
				INSERT INTO comments (inv_id, user, date, inversion_com, bp_com, evolutionary_history_com) 
					SELECT working_inv_id, user_id_val, CURDATE(),  inversion_com, bp_com, evolutionary_history_com 
						FROM   comments s1
						WHERE FIND_IN_SET(inv_id, old_comm_list) > 0 OR inv_id = optional_id
							AND comment_id=(SELECT MAX(s2.comment_id)
								FROM comments s2
								WHERE s1.inv_id = s2.inv_id);

			END IF;

		# 13.STATUS
			IF old_status_id IS NOT NULL THEN
				SELECT status INTO old_status_val FROM inversions WHERE id = working_inv_id;
				SET new_status_val = old_status_id;
				UPDATE inversions 
					SET status = new_status_val
				WHERE id = working_inv_id LIMIT 1000 ;

				SET task_val = CONCAT('UPDATE status of inv ', working_inv_id);
				CALL  save_log(user_id_val, task_val, old_status_val, new_status_val);
			
			
			END IF;
		# Return working inv id
			
		#	SELECT working_inv_id ;

		ELSE # Basic checkpoint to asess that the previous step is complete (We have no working inversion id)
			SELECT CONCAT('No inversion merge completed');
		END IF;
	END IF;
ELSE # Basic checkpoint (If there are not inversions)
	SELECT CONCAT('No inversion list'); 
END IF;
# FOREIGN KEY
SET FOREIGN_KEY_CHECKS=1;

END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP PROCEDURE IF EXISTS `redo_gene_relation_allBPs` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8 */ ;
/*!50003 SET character_set_results = utf8 */ ;
/*!50003 SET collation_connection  = utf8_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = '' */ ;
DELIMITER ;;
CREATE DEFINER=`invfest_admin`@`localhost` PROCEDURE `redo_gene_relation_allBPs`()
BEGIN



  DECLARE no_more_rows_inversion BOOLEAN;
  DECLARE loop_cntr_inversion INT DEFAULT 0;
  DECLARE num_rows_inversion INT DEFAULT 0; 
  DECLARE i INT; 

DECLARE bp_id CURSOR FOR SELECT id FROM breakpoints;

OPEN bp_id;
SELECT FOUND_ROWS() INTO num_rows_inversion;

WHILE loop_cntr_inversion < num_rows_inversion DO 

FETCH  bp_id INTO i;

CALL get_inv_gene_realtion(i);


SET loop_cntr_inversion = loop_cntr_inversion + 1; 									
END WHILE ;




    END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP PROCEDURE IF EXISTS `revise_complexity` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8 */ ;
/*!50003 SET character_set_results = utf8 */ ;
/*!50003 SET collation_connection  = utf8_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = '' */ ;
DELIMITER ;;
CREATE DEFINER=`invfest_admin`@`localhost` PROCEDURE `revise_complexity`( inversion_id_val VARCHAR(255),  user_id_val INT )
    SQL SECURITY INVOKER
BEGIN

# DECLARE VARIABLES

# Basics
DECLARE working_inv_id INT;
DECLARE previous_value_val text;
DECLARE newer_value_val text; 
DECLARE task_val text;

DECLARE loop_cntr INT DEFAULT 0;
DECLARE num_rows INT DEFAULT 0;
DECLARE loop_invs INT DEFAULT 0;
DECLARE num_invs INT DEFAULT 0;
		 

DECLARE chr_val VARCHAR(255); 
DECLARE RBP1s_val INT;
DECLARE RBP1e_val INT;
DECLARE RBP2s_val INT;
DECLARE RBP2e_val INT;

DECLARE amount_merged_pred INT; 
DECLARE comp INT; 

DECLARE nothing INT(11);


# PREDI CUR NEW <- select the coordinates to compare the predictions with themselves (from the new inversion), EXCLUDE NOT TRANSLATED

  DECLARE predi_cur_new CURSOR FOR	
	SELECT DISTINCT chr, 
		IF(  research_name = "Martinez-Fundichely et al. 2013", BP1s,  RBP1s), 
		IF(  research_name = "Martinez-Fundichely et al. 2013", BP1e,  RBP1e), 
		IF(  research_name = "Martinez-Fundichely et al. 2013", BP2s,  RBP2s), 
		IF(  research_name = "Martinez-Fundichely et al. 2013", BP2e,  RBP2e)
		FROM predictions
		WHERE inv_id = working_inv_id 
			AND (  accuracy NOT LIKE ('%unlifted%') OR accuracy IS NULL)
			AND (status NOT LIKE ('%FILTERED%') OR status IS NULL);


DECLARE invs CURSOR for
	SELECT id FROM inversions;
    
DECLARE invs_id CURSOR for 
	SELECT id FROM inversions WHERE FIND_IN_SET(id , inversion_id_val)>0;

# PREDICTIONS MERGED CUR <- find all the predictions in the new inversion overlapping with the query prediction
	DECLARE predictions_merged_cur CURSOR FOR
		SELECT COUNT(*) AS d
		FROM predictions AS T
		WHERE(
						( RBP1s_val BETWEEN T.RBP1s AND T.RBP1e) 
						OR 
						( RBP1e_val BETWEEN T.RBP1s AND T.RBP1e )
						OR
						( (RBP1s_val <= T.RBP1s)  AND (RBP1e_val >= T.RBP1e) )
						) 
				AND(
						( RBP2s_val BETWEEN T.RBP2s AND T.RBP2e) 
						OR 
						( RBP2e_val BETWEEN T.RBP2s AND T.RBP2e )
						OR
						( (RBP2s_val <= T.Rbp2s) AND (RBP2e_val >= T.RBP2e))
						)	
		AND chr_val = T.chr
        AND (  accuracy NOT LIKE ('%unlifted%') OR accuracy IS NULL)
		AND (status NOT LIKE ('%FILTERED%') OR status IS NULL)
		AND T.inv_id = working_inv_id;



SET FOREIGN_KEY_CHECKS=0;
 

			SET loop_cntr = 0;
			SET num_rows = 0;
			SET comp = 0;
			SET loop_invs = 0;
			SET num_invs = 0;
			# All compared with the others
		IF inversion_id_val IS NULL THEN
        		OPEN invs;
		ELSE 
				OPEN invs_id;
		END IF;
		
			SELECT FOUND_ROWS() INTO num_invs;

			WHILE loop_invs < num_invs DO
            
				IF inversion_id_val IS NULL THEN
					FETCH invs INTO working_inv_id;
				ELSE 
					FETCH invs_id INTO working_inv_id;
				END IF;
				
				
				OPEN  predi_cur_new;
					SELECT FOUND_ROWS() INTO num_rows;
					WHILE loop_cntr < num_rows DO 
						FETCH  predi_cur_new INTO chr_val, RBP1s_val, RBP1e_val, RBP2s_val, RBP2e_val;
				
						OPEN predictions_merged_cur;
						FETCH predictions_merged_cur
						INTO  amount_merged_pred;
						CLOSE predictions_merged_cur;
						
						# If a comparison is not complete, it is complex
						IF amount_merged_pred < num_rows THEN 	
							SET comp = 1;
						END IF;
								
						SET loop_cntr = loop_cntr + 1; 
					END WHILE; 
				CLOSE predi_cur_new;

				# Restart loop
				SET loop_cntr = 0;
				SET num_rows = 0;

				# Declare the complexity
				IF comp > 0 THEN
					SELECT complexity INTO previous_value_val FROM inversions WHERE id = working_inv_id;
					IF previous_value_val = 'Complex' THEN	
						SET nothing = 0;
					ELSE
						UPDATE inversions SET complexity = 'Complex' WHERE id = working_inv_id;
						SELECT complexity INTO newer_value_val FROM inversions WHERE id = working_inv_id;
						SET task_val = CONCAT('UPDATE complexity of inv ',working_inv_id);
						CALL  save_log(user_id_val, task_val, previous_value_val, newer_value_val);
					END IF;
				ELSE 
					SELECT complexity INTO previous_value_val FROM inversions WHERE id = working_inv_id;
					IF previous_value_val = 'Complex' THEN
						UPDATE inversions SET complexity = NULL WHERE id = working_inv_id;
						SELECT complexity INTO newer_value_val FROM inversions WHERE id = working_inv_id;
						SET task_val = CONCAT('UPDATE complexity of inv ',working_inv_id);
						CALL  save_log(user_id_val, task_val, previous_value_val, newer_value_val);
					END IF;
				END IF;
			SET comp = 0;
			SET loop_invs = loop_invs+1;  
			END WHILE;
		IF inversion_id_val IS NULL THEN
			CLOSE invs;
		ELSE 
			CLOSE invs_id;
		END IF;

SET FOREIGN_KEY_CHECKS=1;

END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP PROCEDURE IF EXISTS `save_log` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8 */ ;
/*!50003 SET character_set_results = utf8 */ ;
/*!50003 SET collation_connection  = utf8_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_AUTO_VALUE_ON_ZERO' */ ;
DELIMITER ;;
CREATE DEFINER=`invfest_admin`@`localhost` PROCEDURE `save_log`(IN user_id_val INT, IN task_val text, IN previous_value_val text, IN newer_value_val text)
    SQL SECURITY INVOKER
BEGIN
	DECLARE cur_time  TIMESTAMP ;

	SET cur_time = CURRENT_TIMESTAMP();
	
	INSERT INTO log_task(user_id, task, previous_value, newer_value, date)  VALUES (user_id_val, task_val, previous_value_val, newer_value_val, cur_time);	











	


END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP PROCEDURE IF EXISTS `setup_korbel_validation` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8 */ ;
/*!50003 SET character_set_results = utf8 */ ;
/*!50003 SET collation_connection  = utf8_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_AUTO_VALUE_ON_ZERO' */ ;
DELIMITER ;;
CREATE DEFINER=`invfest_admin`@`localhost` PROCEDURE `setup_korbel_validation`(IN `prediction_id_val` int, IN `individual_code_val` VARCHAR(255), IN `celera_valid_val` int , IN `fish_valid_val` int, IN `PCR_valid_val` int , IN `mechanism_val` VARCHAR(255), IN user_id_val INT)
    SQL SECURITY INVOKER
BEGIN

 
  DECLARE inv_id_val INT;
  DECLARE pred_id_val INT;
	 DECLARE pred_research_name_val VARCHAR(255);

	DECLARE inversion_status_val  VARCHAR(255) DEFAULT 'possible_TRUE';
  DECLARE predition_status_val  VARCHAR(255) DEFAULT 'possible_TRUE';


  DECLARE no_more_rows_inversion BOOLEAN;
  DECLARE loop_cntr_inversion INT DEFAULT 0;
  DECLARE num_rows_inversion INT DEFAULT 0; 

  DECLARE no_more_rows_inv BOOLEAN;
  DECLARE loop_cntr_inv INT DEFAULT 0;
  DECLARE num_rows_inv INT DEFAULT 0; 

	DECLARE current_inv_status_val  VARCHAR(255);
	DECLARE current_pred_status_val  VARCHAR(255);


	
	DECLARE research_name_val VARCHAR(255) DEFAULT 'Korbel';
	DECLARE valiadtion_method_val  VARCHAR(255);



 DECLARE prediction_cur CURSOR FOR
    SELECT
        id, research_name
    FROM predictions
		WHERE inv_id = inv_id_val;


DECLARE inv_cur CURSOR FOR
			SELECT inv_id 
				FROM predictions
				WHERE id = prediction_id_val AND research_name =  research_name_val;


	DECLARE CONTINUE HANDLER FOR NOT FOUND
    SET no_more_rows_inversion = TRUE;
		SET no_more_rows_inv = TRUE;


	
OPEN inv_cur;
				SELECT FOUND_ROWS() INTO num_rows_inv;
WHILE loop_cntr_inv < num_rows_inv DO 

FETCH  inv_cur
INTO inv_id_val;

IF celera_valid_val != 0 THEN 


SET valiadtion_method_val = "Celera comparison";

INSERT INTO validation	(research_name, inv_id, method, status)
											VALUES (research_name_val, inv_id_val, valiadtion_method_val, inversion_status_val);

END IF; 

IF  fish_valid_val != 0 THEN 

SET valiadtion_method_val = "Fish";

INSERT INTO validation	(research_name, inv_id, method, status)
											VALUES (research_name_val, inv_id_val, valiadtion_method_val, inversion_status_val);

SELECT status INTO current_inv_status_val
										FROM inversions
										WHERE id = inv_id_val;
									
									IF current_inv_status_val IS NULL THEN
											UPDATE inversions SET status = inversion_status_val WHERE id = inv_id_val;
									ELSEIF (current_inv_status_val != inversion_status_val) AND (current_inv_status_val = 'FALSE' OR current_inv_status_val = 'possible_FALSE' )THEN
											UPDATE inversions SET status = 'ambiguous validation results' WHERE id = inv_id_val;
									END IF;


END IF; 

IF PCR_valid_val != 0 THEN 

SET valiadtion_method_val = "PCR";

INSERT INTO validation	(research_name, inv_id, method, status)
											VALUES (research_name_val, inv_id_val, valiadtion_method_val, inversion_status_val);	

SELECT status INTO current_inv_status_val
										FROM inversions
										WHERE id = inv_id_val;
									
									IF current_inv_status_val IS NULL THEN
											UPDATE inversions SET status = inversion_status_val WHERE id = inv_id_val;
									ELSEIF (current_inv_status_val != inversion_status_val) AND (current_inv_status_val = 'FALSE' OR current_inv_status_val = 'possible_FALSE' )THEN
											UPDATE inversions SET status = 'ambiguous validation results' WHERE id = inv_id_val;
									END IF;


END IF;  

IF  mechanism_val != 0 THEN 
						
											UPDATE inversions SET origin = CONCAT ('possible ',mechanism_val) WHERE id = inv_id_val AND origin IS NULL ;
	
END IF; 

	
	

	
	
		
	
									


		
				IF (fish_valid_val != 0 ) OR (PCR_valid_val != 0) THEN
	UPDATE predictions SET checking = 'TRUE' WHERE id = prediction_id_val AND research_name =  research_name_val;	
			SET predition_status_val = CONCAT('on_', inversion_status_val);
	
			OPEN prediction_cur;
				SELECT FOUND_ROWS() INTO num_rows_inversion;
			WHILE loop_cntr_inversion < num_rows_inversion DO 

											FETCH  prediction_cur
											INTO	pred_id_val, pred_research_name_val;
											
										SELECT status INTO current_pred_status_val
										FROM predictions
										WHERE id = pred_id_val AND research_name =  pred_research_name_val AND inv_id = inv_id_val;	
									
									IF current_pred_status_val IS NULL THEN
									UPDATE predictions SET status =  predition_status_val WHERE id = pred_id_val AND research_name =  pred_research_name_val AND inv_id = inv_id_val;
									ELSEIF (current_inv_status_val != inversion_status_val) AND (current_inv_status_val = 'FALSE' OR current_inv_status_val = 'possible_FALSE' )THEN
											UPDATE predictions SET status = 'ambiguous validation results' WHERE id = pred_id_val AND research_name =  pred_research_name_val AND inv_id = inv_id_val;
									END IF;


											
										
			SET loop_cntr_inversion = loop_cntr_inversion + 1; 									
			END WHILE ;
			CLOSE prediction_cur;
			
			END IF;

SET loop_cntr_inv = loop_cntr_inv + 1; 									
END WHILE ;





END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP PROCEDURE IF EXISTS `setup_prediction` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8 */ ;
/*!50003 SET character_set_results = utf8 */ ;
/*!50003 SET collation_connection  = utf8_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_AUTO_VALUE_ON_ZERO' */ ;
DELIMITER ;;
CREATE DEFINER=`invfest_admin`@`localhost` PROCEDURE `setup_prediction`(IN `newInv_id_val` int,IN `newInv_chr_val` varchar(255),IN `newInv_bp1s_val` int,IN `newInv_bp1e_val` int,IN `newInv_bp2s_val` int,IN `newInv_bp2e_val` int,IN `newInv_studyName_val` varchar(255), IN user_id_val INT)
    SQL SECURITY INVOKER
BEGIN


	
	DECLARE no_more_rows_merge BOOLEAN;
	DECLARE loop_cntr_merged INT DEFAULT 0;
	DECLARE num_rows_merged INT DEFAULT 0; 

	DECLARE amount_merged_val INT;
  DECLARE inv_id_merged_val INT; 

	DECLARE inv_evidences INT;

	DECLARE newInv_main_id_val INT DEFAULT 0;

  DECLARE newInv_RBP1s_val INT ;
  DECLARE newInv_RBP1e_val INT ;
  DECLARE newInv_RBP2s_val INT ;
  DECLARE newInv_RBP2e_val INT ;

	DECLARE prediction_error_val INT;
	DECLARE num_pred_on_inv INT DEFAULT 0; 
	DECLARE loop_cntr_pred_on_inv  INT DEFAULT 0;
  DECLARE pred_merged VARCHAR(255);
  DECLARE total_pred_merged VARCHAR(255);

	DECLARE same_group INT DEFAULT 0; 





	DECLARE merge_inv_cur CURSOR FOR
		SELECT COUNT(*) AS c , T.inv_id 
		FROM predictions AS T
		WHERE(
						( newInv_RBP1s_val BETWEEN T.RBP1s AND T.RBP1e) 
						OR 
						( newInv_RBP1e_val BETWEEN T.RBP1s AND T.RBP1e )
						OR
						( (newInv_RBP1s_val <= T.RBP1s)  AND (newInv_RBP1e_val >= T.RBP1e) )
						) 
				AND(
						( newInv_RBP2s_val BETWEEN T.RBP2s AND T.RBP2e) 
						OR 
						( newInv_RBP2e_val BETWEEN T.RBP2s AND T.RBP2e )
						OR
						( (newInv_RBP2s_val <= T.RBP2s) AND (newInv_RBP2e_val >= T.RBP2e))
					)	
					
					
					AND newInv_chr_val = T.chr
		GROUP BY T.inv_id
		ORDER BY c DESC;


	DECLARE preditions_merged_cur CURSOR FOR
		SELECT concat(T.research_name, T.research_id) 
		FROM predictions AS T
		WHERE(
						( newInv_RBP1s_val BETWEEN T.RBP1s AND T.RBP1e) 
						OR 
						( newInv_RBP1e_val BETWEEN T.RBP1s AND T.RBP1e )
						OR
						( (newInv_RBP1s_val <= T.RBP1s)  AND (newInv_RBP1e_val >= T.RBP1e) )
						) 
				AND(
						( newInv_RBP2s_val BETWEEN T.RBP2s AND T.RBP2e) 
						OR 
						( newInv_RBP2e_val BETWEEN T.RBP2s AND T.RBP2e )
						OR
						( (newInv_RBP2s_val <= T.Rbp2s) AND (newInv_RBP2e_val >= T.RBP2e))
						)	
					
					
					AND newInv_chr_val = T.chr
				  AND T.inv_id = inv_id_merged_val
			ORDER BY T.research_name, T.research_id;







   
 

				SELECT T.prediction_error INTO prediction_error_val
				FROM researchs AS T
				WHERE  T.name =  newInv_studyName_val;
				
				SET newInv_RBP1s_val = newInv_bp1s_val - prediction_error_val;
				SET newInv_RBP1e_val = newInv_bp1e_val + prediction_error_val;
				SET newInv_RBP2s_val = newInv_bp2s_val - prediction_error_val;
				SET newInv_RBP2e_val = newInv_bp2e_val + prediction_error_val;
				
				OPEN merge_inv_cur;
					SELECT FOUND_ROWS() INTO num_rows_merged;
					
					IF num_rows_merged = 0 THEN 						
						INSERT INTO inversions	(name, chr, range_start, range_end, size, detected_amount) VALUES (newInv_id_val, newInv_chr_val, newInv_bp1s_val, newInv_bp2e_val, newInv_bp2e_val - newInv_bp1s_val, 1);
						SELECT LAST_INSERT_ID() INTO newInv_main_id_val;
						INSERT INTO predictions	(inv_id, research_name, research_id, chr, BP1s, BP1e, BP2s, BP2e, RBP1s, RBP1e, RBP2s, RBP2e)
							VALUES (newInv_main_id_val, newInv_studyName_val, newInv_id_val, newInv_chr_val, newInv_bp1s_val, newInv_bp1e_val,  newInv_bp2s_val, newInv_bp2e_val, newInv_Rbp1s_val, newInv_Rbp1e_val,  newInv_Rbp2s_val, newInv_Rbp2e_val);
							CALL update_BP(newInv_main_id_val, NULL);

					ELSE 
							
							CREATE TEMPORARY TABLE T_pred_merged_on_inv ( list VARCHAR(255) NOT NULL );	
							
							WHILE loop_cntr_merged < num_rows_merged DO 

									FETCH  merge_inv_cur
									INTO	amount_merged_val, inv_id_merged_val;
  
									SELECT COUNT(*) INTO inv_evidences 
											FROM predictions
											WHERE  inv_id = inv_id_merged_val;

									SET total_pred_merged = "";
									SET same_group = 0;

									OPEN  preditions_merged_cur;
										SELECT FOUND_ROWS() INTO num_pred_on_inv;
											WHILE loop_cntr_pred_on_inv < num_pred_on_inv DO 
												FETCH  preditions_merged_cur
												INTO	pred_merged;
												SET total_pred_merged = concat_ws(',',total_pred_merged, pred_merged);
												SET loop_cntr_pred_on_inv = loop_cntr_pred_on_inv + 1; 
											END WHILE; 
									CLOSE preditions_merged_cur;
		
											SELECT COUNT(*) INTO same_group
													FROM T_pred_merged_on_inv
													WHERE INSTR( list,  total_pred_merged) ;

											IF  same_group = 0 THEN 
														INSERT INTO T_pred_merged_on_inv (list) VALUES (total_pred_merged);	

								
												IF amount_merged_val = inv_evidences THEN 										
													INSERT INTO predictions	(inv_id, research_name, research_id, chr, BP1s, BP1e, BP2s, BP2e, RBP1s, RBP1e, RBP2s, RBP2e)
														VALUES (inv_id_merged_val, newInv_studyName_val, newInv_id_val, newInv_chr_val, newInv_bp1s_val, newInv_bp1e_val,  newInv_bp2s_val, newInv_bp2e_val, newInv_Rbp1s_val, newInv_Rbp1e_val,  newInv_Rbp2s_val, newInv_Rbp2e_val);	
													UPDATE inversions SET detected_amount = detected_amount+1, name =  newInv_id_val WHERE id = inv_id_merged_val;
													CALL update_BP(inv_id, NULL);
							
												ELSE 									
													INSERT INTO inversions	(name, chr, range_start, range_end, size, detected_amount) VALUES (newInv_id_val, newInv_chr_val, newInv_bp1s_val, newInv_bp2e_val, newInv_bp2e_val - newInv_bp1s_val, amount_merged_val+1);
													SELECT LAST_INSERT_ID() INTO newInv_main_id_val;
													INSERT INTO predictions	(inv_id, research_name, research_id, chr, BP1s, BP1e, BP2s, BP2e, RBP1s, RBP1e, RBP2s, RBP2e)
														VALUES (newInv_main_id_val, newInv_studyName_val, newInv_id_val, newInv_chr_val, newInv_bp1s_val, newInv_bp1e_val,  newInv_bp2s_val, newInv_bp2e_val, newInv_Rbp1s_val, newInv_Rbp1e_val,  newInv_Rbp2s_val, newInv_Rbp2e_val);
													INSERT INTO predictions	(inv_id, research_name, research_id, chr, BP1s, BP1e, BP2s, BP2e, RBP1s, RBP1e, RBP2s, RBP2e)
														SELECT newInv_main_id_val, research_name, research_id, chr, BP1s, BP1e, BP2s, BP2e, RBP1s, RBP1e, RBP2s, RBP2e FROM predictions
															WHERE(
																						( newInv_RBP1s_val BETWEEN RBP1s AND RBP1e) 
																						OR 
																						( newInv_RBP1e_val BETWEEN RBP1s AND RBP1e )
																						OR
																						( (newInv_RBP1s_val <= RBP1s)  AND (newInv_RBP1e_val >= RBP1e) )
																						) 
																						AND(
																						( newInv_RBP2s_val BETWEEN RBP2s AND RBP2e) 
																						OR 
																						( newInv_RBP2e_val BETWEEN RBP2s AND RBP2e )
																						OR
																						( (newInv_RBP2s_val <= RBP2s) AND (newInv_RBP2e_val >= RBP2e))
																						)	
																					
																					
																					AND newInv_chr_val = chr
																					AND inv_id = inv_id_merged_val;
												CALL update_BP(newInv_main_id_val, NULL);

												END IF ;

											END IF ; 

												SET loop_cntr_merged = loop_cntr_merged + 1; 
							END WHILE; 	
							DROP TABLE T_pred_merged_on_inv ;	
					END IF;


END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP PROCEDURE IF EXISTS `setup_pred_to_inv_merge` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8 */ ;
/*!50003 SET character_set_results = utf8 */ ;
/*!50003 SET collation_connection  = utf8_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = '' */ ;
DELIMITER ;;
CREATE DEFINER=`invfest_admin`@`localhost` PROCEDURE `setup_pred_to_inv_merge`(IN `newInv_chr_val` varchar(255),IN `newInv_bp1s_val` int,IN `newInv_bp1e_val` int,IN `newInv_bp2s_val` int,IN `newInv_bp2e_val` int,IN `newInv_studyName_val` varchar(255), IN user_id_val INT)
    SQL SECURITY INVOKER
BEGIN

# DECLARE VARIABLES

	DECLARE newInv_RBP1s_val INT ; 
	DECLARE newInv_RBP1e_val INT ; 
	DECLARE newInv_RBP2s_val INT ; 
	DECLARE newInv_RBP2e_val INT ; 
	DECLARE inv_id_merged_val INT; 
	DECLARE prediction_error_val INT;  
	DECLARE name_val VARCHAR(255); 
	DECLARE num_rows_merged INT DEFAULT 0; 
	DECLARE newer_value_val VARCHAR(255); 
	DECLARE newInv_id_val INT; 
	DECLARE newInv_main_id_val INT DEFAULT 0; 
	DECLARE inv_evidences INT; 
	DECLARE task_val TEXT; 
	DECLARE loop_cntr_merged INT DEFAULT 0; 
	DECLARE previous_value_val VARCHAR(255); 
	DECLARE amount_merged_val INT; 
 

# DECLARE CURSOR  merge_inv_cur
	DECLARE merge_inv_cur CURSOR FOR
		SELECT  b.inv_id, COUNT(*) AS c
		FROM inversions i 
		INNER JOIN breakpoints b ON b.id =(SELECT id 
				FROM breakpoints b2 WHERE b2.inv_id = i.id 
				ORDER BY FIELD (b2.definition_method, 'manual curation', 'default informatic definition'),
				b2.id DESC LIMIT 1) 
		INNER JOIN predictions p ON p.inv_id = i.id
		WHERE b.chr = newInv_chr_val 
					AND(
							( newInv_RBP1s_val BETWEEN b.bp1_start AND b.bp1_end ) 
							OR 
							( newInv_RBP1e_val BETWEEN b.bp1_start AND b.bp1_end )
							OR
							( (newInv_RBP1s_val <= b.bp1_start)  AND (newInv_RBP1e_val >=  b.bp1_end) )
							) 
					AND(
							(newInv_RBP2s_val BETWEEN b.bp2_start AND b.bp2_end) 
							OR 
							( newInv_RBP2e_val  BETWEEN b.bp2_start AND b.bp2_end )
							OR
							( (newInv_RBP2s_val <= b.bp2_start) AND (newInv_RBP2e_val  >= b.bp2_end))
						)	
		AND i.status NOT IN ('WITHDRAWN', 'withdrawn' , 'Withdrawn')
		GROUP BY b.inv_id;


# DECLARE CURSOR predictions_merged_cur

	DECLARE predictions_merged_cur CURSOR FOR
		SELECT COUNT(*) AS d
		FROM predictions AS T
		WHERE(
						( newInv_RBP1s_val BETWEEN T.RBP1s AND T.RBP1e) 
						OR 
						( newInv_RBP1e_val BETWEEN T.RBP1s AND T.RBP1e )
						OR
						( (newInv_RBP1s_val <= T.RBP1s)  AND (newInv_RBP1e_val >= T.RBP1e) )
						) 
				AND(
						( newInv_RBP2s_val BETWEEN T.RBP2s AND T.RBP2e) 
						OR 
						( newInv_RBP2e_val BETWEEN T.RBP2s AND T.RBP2e )
						OR
						( (newInv_RBP2s_val <= T.Rbp2s) AND (newInv_RBP2e_val >= T.RBP2e))
						)	
		AND newInv_chr_val = T.chr
		AND T.inv_id = inv_id_merged_val;

# START

	# Save confidence interval

		SELECT prediction_error INTO prediction_error_val
		FROM researchs
		WHERE  name =  newInv_studyName_val;
	
	# Apply confidence interval to coordinates
				
		IF (newInv_bp1e_val -  newInv_bp1s_val < prediction_error_val)  THEN
			SET newInv_RBP1s_val = newInv_bp1e_val  - prediction_error_val;
			SET newInv_RBP1e_val = newInv_bp1s_val + prediction_error_val;
		ELSE
			SET newInv_RBP1s_val = newInv_bp1s_val;
			SET newInv_RBP1e_val = newInv_bp1e_val;
		END IF ;
		IF (newInv_bp2e_val -  newInv_bp2s_val < prediction_error_val)  THEN
			SET newInv_RBP2s_val = newInv_bp2e_val  - prediction_error_val;
			SET newInv_RBP2e_val = newInv_bp2s_val + prediction_error_val;
		ELSE
			SET newInv_RBP2s_val = newInv_bp2s_val;
			SET newInv_RBP2e_val = newInv_bp2e_val;
		END IF ;

	# Serach for number of overlapping inversions
				
		OPEN merge_inv_cur;
		SELECT FOUND_ROWS() INTO num_rows_merged;
				
	# Set possible new inversion name

		SELECT CONCAT('HsInv', SUBSTRING(`name`, -4) +1) INTO  name_val
		FROM inversions
		WHERE `name` LIKE 'HsInv%' ORDER BY `name` DESC  LIMIT 1;

	# Set possible new research ID
	
		SELECT IF(MAX(research_id) IS NULL,1, MAX(research_id)+1) INTO newInv_id_val
		FROM predictions WHERE research_name = newInv_studyName_val;

# PROCESS INFORMATION

	# If it is a new inversion, create with the new name
    				
				IF num_rows_merged = 0 THEN 						
						INSERT INTO inversions	(name, chr, range_start, range_end, size, detected_amount, status) VALUES (name_val, newInv_chr_val, newInv_bp1s_val, newInv_bp2e_val, newInv_bp2s_val - newInv_bp1e_val, 1, 'ND');
						SELECT LAST_INSERT_ID() INTO newInv_main_id_val;
						SET task_val = CONCAT('INSERT 1 new inversions id');
						CALL  save_log(user_id_val, task_val, "none", newInv_main_id_val);
						INSERT INTO predictions	(inv_id, research_name, research_id, chr, BP1s, BP1e, BP2s, BP2e, RBP1s, RBP1e, RBP2s, RBP2e)
							VALUES (newInv_main_id_val, newInv_studyName_val, newInv_id_val, newInv_chr_val, newInv_bp1s_val, newInv_bp1e_val,  newInv_bp2s_val, newInv_bp2e_val, newInv_Rbp1s_val, newInv_Rbp1e_val,  newInv_Rbp2s_val, newInv_Rbp2e_val);
						SELECT LAST_INSERT_ID() INTO newer_value_val;
						SET task_val = CONCAT('INSERT 1 predictions to inv ',newInv_main_id_val);
						CALL  save_log(user_id_val, task_val, "none", newer_value_val);
						CALL update_BP(newInv_main_id_val, NULL, user_id_val);

	# If there are overlapping inversions, create new prediction entry for each one

				ELSE 
	
					WHILE loop_cntr_merged < num_rows_merged DO 

					# Take information from cursors

									FETCH  merge_inv_cur
									INTO	 inv_id_merged_val, inv_evidences;
			
									OPEN predictions_merged_cur;
									FETCH predictions_merged_cur
									INTO  amount_merged_val;
									CLOSE predictions_merged_cur;

					# If not all predictions are overlapping, set it as a complex inversion

									IF amount_merged_val < inv_evidences THEN 	
											SELECT complexity INTO previous_value_val FROM inversions WHERE id = inv_id_merged_val;
											UPDATE inversions SET complexity = 'Complex' WHERE id = inv_id_merged_val;
											SELECT complexity INTO newer_value_val FROM inversions WHERE id = inv_id_merged_val;
											SET task_val = CONCAT('UPDATE complexity of inv ',inv_id_merged_val);
											CALL  save_log(user_id_val, task_val, previous_value_val, newer_value_val);
							
									END IF;
												
					# Update existing inversion

									INSERT INTO predictions	(inv_id, research_name, research_id, chr, BP1s, BP1e, BP2s, BP2e, RBP1s, RBP1e, RBP2s, RBP2e)
										VALUES (inv_id_merged_val, newInv_studyName_val, newInv_id_val, newInv_chr_val, newInv_bp1s_val, newInv_bp1e_val,  newInv_bp2s_val, newInv_bp2e_val, newInv_Rbp1s_val, newInv_Rbp1e_val,  newInv_Rbp2s_val, newInv_Rbp2e_val);	
									SELECT LAST_INSERT_ID() INTO newer_value_val;
									SET task_val = CONCAT('INSERT  prediction to inv ',inv_id_merged_val);
									CALL  save_log(user_id_val, task_val, "none", newer_value_val);
													
									SELECT detected_amount INTO previous_value_val FROM inversions WHERE id = inv_id_merged_val;
									UPDATE inversions SET detected_amount = detected_amount+1 WHERE id = inv_id_merged_val;
									SELECT detected_amount INTO newer_value_val FROM inversions WHERE id = inv_id_merged_val;
									SET task_val = CONCAT('UPDATE detected_amount of inv ',inv_id_merged_val);
									CALL  save_log(user_id_val, task_val, previous_value_val, newer_value_val);
							
									CALL update_BP(inv_id_merged_val, NULL, user_id_val);													
									
					# Update control variable
									SET loop_cntr_merged = loop_cntr_merged + 1; 
					END WHILE; 	
					
				END IF;

END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP PROCEDURE IF EXISTS `setup_validation` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8 */ ;
/*!50003 SET character_set_results = utf8 */ ;
/*!50003 SET collation_connection  = utf8_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_AUTO_VALUE_ON_ZERO' */ ;
DELIMITER ;;
CREATE DEFINER=`invfest_admin`@`localhost` PROCEDURE `setup_validation`(IN `inv_name_val` VARCHAR(255), IN `research_name_val` VARCHAR(255), IN `validation_val` VARCHAR(255), IN `valiadtion_method_val` VARCHAR(255), IN `PCRconditions_val` VARCHAR(255), IN `primer_val` VARCHAR(255),IN `validation_comment_val` VARCHAR(255) ,IN `BAC_val` VARCHAR(255) ,IN `BAC_result_val` VARCHAR(255), IN user_id_val INT)
    SQL SECURITY INVOKER
BEGIN

 
  DECLARE inv_id_val INT;
  DECLARE pred_id_val INT;
	 DECLARE pred_research_name_val VARCHAR(255);

	DECLARE inversion_status_val  VARCHAR(255);
  DECLARE predition_status_val  VARCHAR(255);


  DECLARE no_more_rows_inversion BOOLEAN;
  DECLARE loop_cntr_inversion INT DEFAULT 0;
  DECLARE num_rows_inversion INT DEFAULT 0; 


	DECLARE current_inv_status_val  VARCHAR(255);

	


  DECLARE prediction_cur CURSOR FOR
    SELECT
        research_id, research_name
    FROM predictions
		WHERE inv_id = inv_id_val;


	DECLARE CONTINUE HANDLER FOR NOT FOUND
    SET no_more_rows_inversion = TRUE;



 SELECT id INTO inv_id_val
				FROM inversions
				WHERE  name =  inv_name_val;

	INSERT INTO validation	(research_name, inv_id, method, status, experimental_conditions, primers, comment)
											VALUES (research_name_val, inv_id_val, valiadtion_method_val, validation_val, PCRconditions_val, primer_val, validation_comment_val);	

	IF 	BAC_val != '-' THEN
				INSERT INTO validation	(research_name, inv_id, method, status, comment, HG_BAC, BAC_result)
											VALUES (research_name_val, inv_id_val, valiadtion_method_val, validation_val, validation_comment_val, BAC_val, BAC_result_val);	
	END IF;						
									
SET inversion_status_val = validation_val;

		SELECT status INTO current_inv_status_val
										FROM inversions
										WHERE id = inv_id_val;
									
									IF current_inv_status_val IS NULL THEN
											UPDATE inversions SET status = inversion_status_val WHERE id = inv_id_val;
									ELSEIF (current_inv_status_val != inversion_status_val) AND ((current_inv_status_val = 'TRUE') OR (current_inv_status_val = 'FALSE') )THEN
											UPDATE inversions SET status = 'ambiguous checking results' WHERE id = inv_id_val;
									ELSE
											UPDATE inversions SET status = inversion_status_val WHERE id = inv_id_val;
									END IF;


	
	SET predition_status_val = CONCAT('on_', inversion_status_val);
	

OPEN prediction_cur;
	SELECT FOUND_ROWS() INTO num_rows_inversion;
WHILE loop_cntr_inversion < num_rows_inversion DO 

									FETCH  prediction_cur
									INTO	pred_id_val, pred_research_name_val;
 
										UPDATE predictions SET status =  predition_status_val WHERE research_id = pred_id_val AND research_name =  pred_research_name_val;	

										
SET loop_cntr_inversion = loop_cntr_inversion + 1; 									
END WHILE ;



END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP PROCEDURE IF EXISTS `set_individual_inv_data` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8 */ ;
/*!50003 SET character_set_results = utf8 */ ;
/*!50003 SET collation_connection  = utf8_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_AUTO_VALUE_ON_ZERO' */ ;
DELIMITER ;;
CREATE DEFINER=`invfest_admin`@`localhost` PROCEDURE `set_individual_inv_data`(IN `indvidual_code_val` VARCHAR(255), IN `inv_id_val` INT(11), IN `genotype_val` VARCHAR(255), IN `primer_val` VARCHAR(255), IN `BAC_val` VARCHAR(255), IN user_id_val INT)
    SQL SECURITY INVOKER
BEGIN
	
 
	DECLARE individual_id_val INT;
  DECLARE valid_id_val INT;
  DECLARE validation_research_name_val VARCHAR(255);


SELECT id INTO individual_id_val
				FROM individuals
				WHERE  code =  indvidual_code_val;

	SELECT id, research_name INTO valid_id_val, validation_research_name_val
				FROM validation
				WHERE  inv_id =  inv_id_val AND primers = primer_val;


IF not EXISTS (SELECT * FROM individuals_detection WHERE individuals_id = individual_id_val AND  inversions_id = inv_id_val AND validation_id = valid_id_val)  THEN
			INSERT INTO individuals_detection	(individuals_id, inversions_id, validation_id, validation_research_name, genotype)
											VALUES (individual_id_val, inv_id_val, valid_id_val, validation_research_name_val, genotype_val);
END IF;

IF 	BAC_val != '-' THEN

	SELECT id, research_name INTO valid_id_val, validation_research_name_val
				FROM validation
				WHERE  HG_BAC = BAC_val;

							IF not EXISTS (SELECT * FROM individuals_detection WHERE individuals_id = 1 AND  inversions_id = inv_id_val AND validation_id = valid_id_val)  THEN
										INSERT INTO individuals_detection	(individuals_id, inversions_id, validation_id, validation_research_name, genotype)
											VALUES (1, inv_id_val, valid_id_val, validation_research_name_val, "inverted");
							END IF;
	END IF;			

END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP PROCEDURE IF EXISTS `set_prediction_checking` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8 */ ;
/*!50003 SET character_set_results = utf8 */ ;
/*!50003 SET collation_connection  = utf8_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_AUTO_VALUE_ON_ZERO' */ ;
DELIMITER ;;
CREATE DEFINER=`invfest_admin`@`localhost` PROCEDURE `set_prediction_checking`(IN `prediction_id_val` int, IN `research_name_val` VARCHAR(255), IN `checking_val` VARCHAR(255), IN `checking_type_val` VARCHAR(255), IN user_id_val INT)
    SQL SECURITY INVOKER
BEGIN

 
  DECLARE inv_id_val INT;
  DECLARE pred_id_val INT;

	DECLARE inversion_status_val  VARCHAR(255);
  DECLARE checking_comment_val VARCHAR(255);


  DECLARE no_more_rows_inversion BOOLEAN;
  DECLARE loop_cntr_inversion INT DEFAULT 0;
  DECLARE num_rows_inversion INT DEFAULT 0; 


	DECLARE current_inv_status_val  VARCHAR(255);

	


  DECLARE prediction_cur CURSOR FOR
    SELECT
        inv_id
    FROM predictions
		WHERE research_id = prediction_id_val AND research_name =  research_name_val;


	DECLARE CONTINUE HANDLER FOR NOT FOUND
    SET no_more_rows_inversion = TRUE;


	UPDATE predictions SET checking = checking_val WHERE research_id = prediction_id_val AND research_name =  research_name_val;	

	SET inversion_status_val = CONCAT('possible_', checking_val);
	SET checking_comment_val = CONCAT('called as possible ', checking_val, ' in ', research_name_val,' by ', checking_type_val, '.',CHAR(10 using utf8));
	

OPEN prediction_cur;
	SELECT FOUND_ROWS() INTO num_rows_inversion;
WHILE loop_cntr_inversion < num_rows_inversion DO 

									FETCH  prediction_cur
									INTO	inv_id_val;
 
									INSERT INTO validation	(inv_id, type, status, comment)
											VALUES (inv_id_val, checking_type_val, inversion_status_val, checking_comment_val);	
									
									SELECT status INTO current_inv_status_val
										FROM inversions
										WHERE id = inv_id_val;
									
									IF current_inv_status_val IS NULL THEN
											UPDATE inversions SET status = inversion_status_val WHERE id = inv_id_val;
									ELSEIF (current_inv_status_val != inversion_status_val) AND (current_inv_status_val != 'TRUE') AND (current_inv_status_val != 'FALSE') THEN
											UPDATE inversions SET status = 'ambiguous checking results' WHERE id = inv_id_val;		
									END IF;
										
SET loop_cntr_inversion = loop_cntr_inversion + 1; 									
END WHILE ;



END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP PROCEDURE IF EXISTS `set_pred_check_modif` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8 */ ;
/*!50003 SET character_set_results = utf8 */ ;
/*!50003 SET collation_connection  = utf8_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_AUTO_VALUE_ON_ZERO' */ ;
DELIMITER ;;
CREATE DEFINER=`invfest_admin`@`localhost` PROCEDURE `set_pred_check_modif`(IN `prediction_id_val` int, IN `research_name_val` VARCHAR(255), IN `checking_val` VARCHAR(255), IN `checking_type_val` VARCHAR(255), IN user_id_val INT)
    SQL SECURITY INVOKER
BEGIN

 
  DECLARE inv_id_val INT;
  DECLARE pred_id_val INT;

	DECLARE inversion_status_val  VARCHAR(255);
  DECLARE checking_comment_val VARCHAR(255);


  DECLARE no_more_rows_inversion BOOLEAN;
  DECLARE loop_cntr_inversion INT DEFAULT 0;
  DECLARE num_rows_inversion INT DEFAULT 0; 


	DECLARE current_inv_status_val  VARCHAR(255);

	


  DECLARE prediction_cur CURSOR FOR
    SELECT
        inv_id
    FROM predictions
		WHERE research_id = prediction_id_val AND research_name =  research_name_val;


	DECLARE CONTINUE HANDLER FOR NOT FOUND
    SET no_more_rows_inversion = TRUE;


	UPDATE predictions SET checking = checking_val WHERE research_id = prediction_id_val AND research_name =  research_name_val;	

	SET inversion_status_val = CONCAT('possible_', checking_val);
	SET checking_comment_val = CONCAT('called as possible ', checking_val, ' in ', research_name_val,' by ', checking_type_val, '.',CHAR(10 using utf8));
	

OPEN prediction_cur;
	SELECT FOUND_ROWS() INTO num_rows_inversion;
WHILE loop_cntr_inversion < num_rows_inversion DO 

									FETCH  prediction_cur
									INTO	inv_id_val;
 
									INSERT INTO validation	(inv_id, type, status, comment)
											VALUES (inv_id_val, checking_type_val, inversion_status_val, checking_comment_val);	
									
									SELECT status INTO current_inv_status_val
										FROM inversions
										WHERE id = inv_id_val;
									
									IF current_inv_status_val IS NULL THEN
											UPDATE inversions SET status = inversion_status_val WHERE id = inv_id_val;
									ELSEIF (current_inv_status_val != inversion_status_val) AND (current_inv_status_val != 'TRUE') AND (current_inv_status_val != 'FALSE') THEN
											UPDATE inversions SET status = 'ambiguous checking results' WHERE id = inv_id_val;		
									END IF;
										
SET loop_cntr_inversion = loop_cntr_inversion + 1; 									
END WHILE ;



END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP PROCEDURE IF EXISTS `simpleproc` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = latin1 */ ;
/*!50003 SET character_set_results = latin1 */ ;
/*!50003 SET collation_connection  = latin1_swedish_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = '' */ ;
DELIMITER ;;
CREATE DEFINER=`invfest_admin`@`localhost` PROCEDURE `simpleproc`(OUT param1 INT)
begin  select count(*) into param1 from inversions;END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP PROCEDURE IF EXISTS `split_inv` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8 */ ;
/*!50003 SET character_set_results = utf8 */ ;
/*!50003 SET collation_connection  = utf8_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = '' */ ;
DELIMITER ;;
CREATE DEFINER=`invfest_admin`@`localhost` PROCEDURE `split_inv`(IN `old_inv_id_val` INT, IN new_inv1_pred_list varchar(255), IN new_inv2_pred_list varchar(255),  IN new_inv1_valid_list varchar(255), IN new_inv2_valid_list varchar(255), IN inv1_status_val varchar(255), In inv2_status_val varchar(255), IN user_id_val INT)
BEGIN


DECLARE previous_value_val text;
DECLARE newer_value_val text;
DECLARE task_val text;

DECLARE new_Inv1_name VARCHAR(255);
DECLARE new_Inv2_name VARCHAR(255);
DECLARE new_Inv1_id INT;
DECLARE new_Inv2_id INT;
	
DECLARE old_Inv_name_val VARCHAR(255);
DECLARE history_cause_val VARCHAR(255);


DECLARE INquery varchar(255);

DECLARE new_amount_merged_val INT;
DECLARE inv_id_merged_val INT; 

DECLARE inv_evidences INT;




DECLARE num_pred_on_inv INT DEFAULT 0; 
DECLARE loop_cntr_pred_on_inv  INT DEFAULT 0;

DECLARE total_pred_merged VARCHAR(255);

DECLARE same_group INT DEFAULT 0;

DECLARE research_name_val VARCHAR(255);
DECLARE research_id_val INT;
DECLARE inv_id_val INT;
DECLARE chr_val VARCHAR(255);
DECLARE BP1s_val INT;
DECLARE BP1e_val INT;
DECLARE BP2s_val INT;
DECLARE BP2e_val INT;
DECLARE RBP1s_val INT;
DECLARE RBP1e_val INT;
DECLARE RBP2s_val INT;
DECLARE RBP2e_val INT;
DECLARE pstatus_val VARCHAR(255);
DECLARE paccuracy_val VARCHAR(255);
DECLARE pchecking_val VARCHAR(255);
DECLARE pcomments_val TEXT;
DECLARE psupport_val INT;
DECLARE psupport_bp2_val INT;
DECLARE pscore1_val FLOAT;
DECLARE pscore2_val FLOAT;


DECLARE vali_id_val VARCHAR(255);
DECLARE method_val VARCHAR(255);
DECLARE status_val VARCHAR(255);
DECLARE experimental_conditions_val VARCHAR(255);
DECLARE primers_val VARCHAR(255);
DECLARE comment_val TEXT ;
DECLARE checked_val VARCHAR(255);
DECLARE bp_id_val VARCHAR(255);


DECLARE new_predi_id_val INT;
DECLARE new_vali_id_val INT;

DECLARE tab VARCHAR(255);
DECLARE loop_cntr INT DEFAULT 0;
DECLARE num_rows INT DEFAULT 0;
DECLARE table_pred1_cur CURSOR FOR SELECT * FROM temp_data;
DECLARE table_pred2_cur CURSOR FOR SELECT * FROM temp_data;
DECLARE table_val1_cur CURSOR FOR SELECT * FROM temp_data;
DECLARE table_val2_cur CURSOR FOR SELECT * FROM temp_data;
 
# Save the name for the previous inversion
	SELECT `name` INTO old_Inv_name_val FROM inversions WHERE id =  old_inv_id_val;	

# make a new inversion that is equal to the previous one 
INSERT INTO inversions	(chr, range_start, range_end, size, detected_amount) 
	SELECT chr, range_start, range_end, size, detected_amount FROM inversions WHERE id =  old_inv_id_val;

	SELECT LAST_INSERT_ID() INTO new_Inv1_id; 
	SET task_val = CONCAT('INSERT new inversions ');
	CALL  save_log(user_id_val, task_val, "none", new_Inv1_id);

# If there are predictions for this new inversion...

IF new_inv1_pred_list != 'NA' THEN
	SET tab = 'predictions';
	SET @entry = CONCAT("'",new_inv1_pred_list,"'");
	DROP VIEW IF EXISTS temp_data;
	SET @myquery = CONCAT('CREATE VIEW temp_data AS SELECT DISTINCT research_name, research_id, chr, BP1s, BP1e, BP2s, BP2e, RBP1s, RBP1e, RBP2s, RBP2e, status, accuracy, checking, comments, support, support_bp2, score1, score2 FROM ', tab ,' WHERE FIND_IN_SET(id,', @entry, ') > 0'); 
	PREPARE stmt from @myquery; 
	EXECUTE stmt;

	OPEN table_pred1_cur;
		SELECT FOUND_ROWS() INTO num_rows;
		SET new_amount_merged_val = num_rows;
			WHILE loop_cntr < num_rows DO 
				FETCH  table_pred1_cur INTO research_name_val, research_id_val, chr_val, BP1s_val, BP1e_val, BP2s_val, BP2e_val, RBP1s_val, RBP1e_val, RBP2s_val, RBP2e_val, pstatus_val, paccuracy_val, pchecking_val, pcomments_val, psupport_val, psupport_bp2_val, pscore1_val, pscore2_val;
				INSERT INTO predictions (inv_id, research_name, research_id, chr, BP1s, BP1e, BP2s, BP2e, RBP1s, RBP1e, RBP2s, RBP2e, status, accuracy, checking, comments, support, support_bp2, score1, score2) 
					   VALUE (new_Inv1_id, research_name_val, research_id_val, chr_val, BP1s_val, BP1e_val, BP2s_val, BP2e_val, RBP1s_val, RBP1e_val, RBP2s_val, RBP2e_val, pstatus_val, paccuracy_val, pchecking_val, pcomments_val, psupport_val, psupport_bp2_val, pscore1_val, pscore2_val);


		SELECT LAST_INSERT_ID() INTO new_predi_id_val; 

				INSERT INTO individuals_detection (individuals_id, inversions_id, prediction_research_id, prediction_research_name, prediction_id)
					SELECT ind_det.individuals_id, new_Inv1_id, ind_det.prediction_research_id, ind_det.prediction_research_name, new_predi_id_val  FROM individuals_detection AS ind_det
					WHERE ind_det.inversions_id = old_inv_id_val AND ind_det.prediction_research_id = research_id_val AND ind_det.prediction_research_name = research_name_val;
											
				SET loop_cntr = loop_cntr + 1; 
			END WHILE; 
	CLOSE table_pred1_cur;

DEALLOCATE PREPARE stmt;	
SET loop_cntr = 0;
SET num_rows = 0; 

END IF;

# If there are validations for the new inversion...
IF new_inv1_valid_list != 'NA' THEN
	
	SET tab = 'validation';
	SET @entry = CONCAT("'",new_inv1_valid_list,"'");
	DROP VIEW IF EXISTS temp_data;
	SET @myquery = CONCAT('CREATE VIEW temp_data AS SELECT DISTINCT id, research_name, inv_id, method, status, experimental_conditions, primers, comment, checked, bp_id FROM ',tab,' WHERE FIND_IN_SET(id,', @entry, ') > 0'); 
	PREPARE stmt from @myquery; 
	EXECUTE stmt;
SELECT * FROM temp_data;
	OPEN table_val1_cur;
		SELECT FOUND_ROWS() INTO num_rows;
			WHILE loop_cntr < num_rows DO 
				FETCH  table_val1_cur INTO  vali_id_val, research_name_val , inv_id_val, method_val, status_val, experimental_conditions_val, primers_val, comment_val, checked_val, bp_id_val;
				INSERT INTO validation	(research_name, inv_id, method, status, experimental_conditions, primers, comment, checked, bp_id) 
					   VALUE (research_name_val , new_Inv1_id, method_val, status_val, experimental_conditions_val, primers_val, comment_val, checked_val, bp_id_val);

		SELECT LAST_INSERT_ID() INTO new_vali_id_val; 
				
				INSERT INTO individuals_detection (individuals_id, inversions_id, validation_id, validation_research_name, genotype, allele_comment)
					SELECT ind_det.individuals_id, new_Inv1_id, new_vali_id_val, ind_det.validation_research_name, ind_det.genotype, ind_det.allele_comment  FROM individuals_detection AS ind_det 
					WHERE ind_det.inversions_id = inv_id_val AND ind_det.validation_id = vali_id_val AND ind_det.validation_research_name = research_name_val;
											
				SET loop_cntr = loop_cntr + 1; 
			END WHILE; 
	CLOSE table_val1_cur;
	DEALLOCATE PREPARE stmt;
SET loop_cntr = 0;
SET num_rows = 0; 
END IF;

# Count amount of predictions for the new inversion
	SELECT COUNT(*) INTO new_amount_merged_val  FROM predictions WHERE  inv_id = new_Inv1_id;
# Make name for the new inverison
	SELECT CONCAT('HsInv', SUBSTRING(`name`, -4) +1) INTO  new_Inv1_name
		FROM inversions
		WHERE `name` LIKE 'HsInv%' ORDER BY `name` DESC  LIMIT 1;

	UPDATE inversions SET `name` =  new_Inv1_name,  detected_amount = new_amount_merged_val, `status` = inv1_status_val WHERE id = new_Inv1_id;	

# Repeat the procedure with the new inversion
	INSERT INTO inversions	(chr, range_start, range_end, size, detected_amount) 
							SELECT chr,  range_start, range_end, size, detected_amount FROM inversions WHERE id =  old_inv_id_val;

	SELECT LAST_INSERT_ID() INTO new_Inv2_id;
	SET task_val = CONCAT('INSERT new inversions ');
	CALL  save_log(user_id_val, task_val, "none", new_Inv2_id);

# Predictions
IF new_inv2_pred_list != 'NA' THEN
	SET tab = 'predictions';
	SET @entry = CONCAT("'",new_inv2_pred_list,"'");
	DROP VIEW IF EXISTS temp_data;
	SET @myquery = CONCAT('CREATE VIEW temp_data AS SELECT DISTINCT research_name, research_id, chr, BP1s, BP1e, BP2s, BP2e, RBP1s, RBP1e, RBP2s, RBP2e, status, accuracy, checking, comments, support, support_bp2, score1, score2 FROM ',tab,' WHERE FIND_IN_SET(id, ', @entry,') > 0'); 
	PREPARE stmt from @myquery; 
	EXECUTE stmt;
	OPEN table_pred2_cur;
		SELECT FOUND_ROWS() INTO num_rows;
		SET new_amount_merged_val = num_rows;
			WHILE loop_cntr < num_rows DO 
				FETCH table_pred2_cur INTO research_name_val, research_id_val, chr_val, BP1s_val, BP1e_val, BP2s_val, BP2e_val, RBP1s_val, RBP1e_val, RBP2s_val, RBP2e_val, pstatus_val, paccuracy_val, pchecking_val, pcomments_val, psupport_val, psupport_bp2_val, pscore1_val, pscore2_val;
				INSERT INTO predictions (inv_id, research_name, research_id, chr, BP1s, BP1e, BP2s, BP2e, RBP1s, RBP1e, RBP2s, RBP2e, status, accuracy, checking, comments, support, support_bp2, score1, score2) 
					   VALUE (new_Inv2_id, research_name_val, research_id_val, chr_val, BP1s_val, BP1e_val, BP2s_val, BP2e_val, RBP1s_val, RBP1e_val, RBP2s_val, RBP2e_val, pstatus_val, paccuracy_val, pchecking_val, pcomments_val, psupport_val, psupport_bp2_val, pscore1_val, pscore2_val);


		SELECT LAST_INSERT_ID() INTO new_predi_id_val; 

				INSERT INTO individuals_detection (individuals_id, inversions_id, prediction_research_id, prediction_research_name, prediction_id)
					SELECT ind_det.individuals_id, new_Inv2_id, ind_det.prediction_research_id, ind_det.prediction_research_name, new_predi_id_val  FROM individuals_detection AS ind_det
					WHERE ind_det.inversions_id = old_inv_id_val AND ind_det.prediction_research_id = research_id_val AND ind_det.prediction_research_name = research_name_val;
											
				SET loop_cntr = loop_cntr + 1; 
			END WHILE; 
  CLOSE table_pred2_cur;
  DEALLOCATE PREPARE stmt;	

SET loop_cntr = 0;
SET num_rows = 0; 

END IF;

# Validations
IF new_inv2_valid_list != 'NA' THEN
	
	SET tab = 'validation';
	SET @entry = CONCAT("'",new_inv2_valid_list,"'");
	DROP VIEW IF EXISTS temp_data;
	SET @myquery = CONCAT('CREATE VIEW temp_data AS SELECT DISTINCT id, research_name, inv_id, method, status, experimental_conditions, primers, comment, checked, bp_id FROM ',tab,' WHERE FIND_IN_SET(id,' ,@entry,') > 0'); 
	PREPARE stmt from @myquery; 
	EXECUTE stmt;
	DEALLOCATE PREPARE stmt;
	
	OPEN  table_val2_cur;
		SELECT FOUND_ROWS() INTO num_rows;
			WHILE loop_cntr < num_rows DO 
				FETCH  table_val2_cur INTO vali_id_val, research_name_val , inv_id_val, method_val, status_val, experimental_conditions_val, primers_val, comment_val, checked_val, bp_id_val;
				INSERT INTO validation	(research_name, inv_id, method, status, experimental_conditions, primers, comment, checked, bp_id) 
					   VALUE (research_name_val , new_Inv2_id, method_val, status_val, experimental_conditions_val, primers_val, comment_val, checked_val, bp_id_val);


		SELECT LAST_INSERT_ID() INTO new_vali_id_val; 
				
				INSERT INTO individuals_detection (individuals_id, inversions_id, validation_id, validation_research_name, genotype, allele_comment)
					SELECT ind_det.individuals_id, new_Inv2_id, new_vali_id_val, ind_det.validation_research_name, ind_det.genotype, ind_det.allele_comment  FROM individuals_detection AS ind_det 
					WHERE ind_det.inversions_id = inv_id_val AND ind_det.validation_id = vali_id_val AND ind_det.validation_research_name = research_name_val;
											
				SET loop_cntr = loop_cntr + 1; 
			END WHILE; 
	CLOSE table_val2_cur;

DROP VIEW temp_data; 
SET loop_cntr = 0;
SET num_rows = 0; 	
END IF;

# Amount of predictions
	SELECT COUNT(*) INTO new_amount_merged_val  FROM predictions WHERE  inv_id = new_Inv2_id;
# Inversion name
	SELECT CONCAT('HsInv', SUBSTRING(`name`, -4) +1) INTO  new_Inv2_name
		FROM inversions
		WHERE `name` LIKE 'HsInv%' ORDER BY `name` DESC  LIMIT 1;
	UPDATE inversions SET `name` =  new_Inv2_name,  detected_amount = new_amount_merged_val , `status` = inv2_status_val WHERE id = new_Inv2_id;	


# Save history
	SET history_cause_val = CONCAT('<a href="report.php?q=',old_inv_id_val,'">',old_Inv_name_val,'</a> split into <a href="report.php?q=',new_Inv1_id,'">',new_Inv1_name,'</a> and <a href="report.php?q=',new_Inv2_id,'">',new_Inv2_name,'</a>');
	INSERT INTO inversion_history	(previous_inv_id, new_inv_id, cause) VALUE (old_inv_id_val, new_Inv1_id, history_cause_val );
	INSERT INTO inversion_history	(previous_inv_id, new_inv_id, cause) VALUE (old_inv_id_val, new_Inv2_id, history_cause_val );

# Tag previous inversion
	SELECT status INTO previous_value_val FROM inversions	WHERE id = old_inv_id_val;
	UPDATE inversions SET status =  "withdrawn" WHERE id = old_inv_id_val;
	
	SELECT status INTO newer_value_val FROM inversions	WHERE id = old_inv_id_val;
	SET task_val = CONCAT('UPDATE status of inv ',old_inv_id_val);
	CALL  save_log(user_id_val, task_val, previous_value_val, newer_value_val);


# The breakpoints are updated
	CALL update_BP(new_Inv1_id, NULL, 'SPLIT', user_id_val);
	CALL update_BP(new_Inv2_id, NULL, 'SPLIT',user_id_val);

# the complexity is updated
	CALL revise_complexity(new_Inv1_id,user_id_val);
    CALL revise_complexity(new_Inv2_id,user_id_val);
	

END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP PROCEDURE IF EXISTS `test` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8 */ ;
/*!50003 SET character_set_results = utf8 */ ;
/*!50003 SET collation_connection  = utf8_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = '' */ ;
DELIMITER ;;
CREATE DEFINER=`invfest_admin`@`localhost` PROCEDURE `test`(in c int)
    SQL SECURITY INVOKER
BEGIN



DECLARE tab text;
DECLARE new_inv1_pred_list text;
SET	new_inv1_pred_list = '1,2,3';

SET tab = 'predictions';

SET @entry = CONCAT("'",new_inv1_pred_list,"'");

DROP VIEW IF EXISTS temp_data;

	SET @myquery = CONCAT('CREATE VIEW temp_data AS SELECT DISTINCT research_name, research_id, chr, BP1s, BP1e, BP2s, BP2e, RBP1s, RBP1e, RBP2s, RBP2e FROM ', tab ,' WHERE FIND_IN_SET(id, ',@entry,') > 0'); 
	SELECT @myquery; 
	PREPARE stmt from @myquery; 
	EXECUTE stmt;
	DEALLOCATE PREPARE stmt;	

DROP VIEW IF EXISTS temp_data;












  


 
  





 







  

	
		

  




																					



END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP PROCEDURE IF EXISTS `test_prdiction_boot` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8 */ ;
/*!50003 SET character_set_results = utf8 */ ;
/*!50003 SET collation_connection  = utf8_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_AUTO_VALUE_ON_ZERO' */ ;
DELIMITER ;;
CREATE DEFINER=`invfest_admin`@`localhost` PROCEDURE `test_prdiction_boot`(IN `newInv_id_val` int,IN `newInv_chr_val` varchar(255),IN `newInv_bp1s_val` int,IN `newInv_bp1e_val` int,IN `newInv_bp2s_val` int,IN `newInv_bp2e_val` int,IN `newInv_studyName_val` varchar(255), IN user_id_val INT)
    SQL SECURITY INVOKER
BEGIN


	
	DECLARE no_more_rows_merge BOOLEAN;
	DECLARE loop_cntr_merged INT DEFAULT 0;
	DECLARE num_rows_merged INT DEFAULT 0; 

	DECLARE amount_merged_val INT;
  DECLARE inv_id_merged_val INT; 
  
  

	DECLARE inv_evidences INT;
	DECLARE same_study_in INT;

	DECLARE newInv_main_id_val INT DEFAULT 0;

	DECLARE x INT;



	DECLARE merge_inv_cur CURSOR FOR
		SELECT COUNT(DISTINCT T.study_name), T.inv_id
		FROM merge_study_to_inv AS T
		WHERE(
						( newInv_bp1s_val BETWEEN T.bp1s AND T.bp1e) 
						OR 
						( newInv_bp1e_val BETWEEN T.bp1s AND T.bp1e )
						OR
						( (newInv_bp1s_val < T.bp1s)  AND (newInv_bp1e_val > T.bp1e) )
						) 
				AND(
						( newInv_bp2s_val BETWEEN T.bp2s AND T.bp2e) 
						OR 
						( newInv_bp2e_val BETWEEN T.bp2s AND T.bp2e )
						OR
						( (newInv_bp2s_val < T.bp2s) AND (newInv_bp2e_val > T.bp2e))
					)	
					AND newInv_bp1e_val <  T.bp2s
					AND newInv_bp2s_val >  T.bp1e  
					AND newInv_chr_val = T.chr
		GROUP BY T.inv_id;

   
	
	
	
	
 
				OPEN merge_inv_cur;
					SELECT FOUND_ROWS() INTO num_rows_merged;
					
					IF num_rows_merged = 0 THEN
						
						INSERT INTO inv_main	(chr, range_start, range_end, size, detected_amount) VALUES (newInv_chr_val, newInv_bp1s_val, newInv_bp2e_val, newInv_bp2e_val - newInv_bp1s_val, 1);
						SELECT LAST_INSERT_ID() INTO newInv_main_id_val;
						INSERT INTO merge_study_to_inv	(inv_id, study_name, study_id, chr, bp1s, bp1e, bp2s, bp2e)
							VALUES (newInv_main_id_val, newInv_studyName_val, newInv_id_val, newInv_chr_val, newInv_bp1s_val, newInv_bp1e_val,  newInv_bp2s_val, newInv_bp2e_val);

					ELSE 
							WHILE loop_cntr_merged < num_rows_merged DO 







									FETCH  merge_inv_cur
									INTO	amount_merged_val, inv_id_merged_val;
  


									SELECT COUNT(*) INTO inv_evidences 
									FROM merge_study_to_inv
									WHERE  inv_id = inv_id_merged_val;
			
									SELECT COUNT(*) INTO same_study_in 
									FROM merge_study_to_inv 
									WHERE(
																	( newInv_bp1s_val BETWEEN bp1s AND bp1e) 
																	OR 
																	( newInv_bp1e_val BETWEEN bp1s AND bp1e )
																	OR
																	( (newInv_bp1s_val < bp1s)  AND (newInv_bp1e_val > bp1e) )
																) 
																AND(
																	( newInv_bp2s_val BETWEEN bp2s AND bp2e) 
																	OR 
																	( newInv_bp2e_val BETWEEN bp2s AND bp2e )
																	OR
																	( (newInv_bp2s_val < bp2s) AND (newInv_bp2e_val > bp2e))
																)	
																AND newInv_bp1e_val <  bp2s
																AND newInv_bp2s_val >  bp1e  
																AND newInv_chr_val = chr
																AND inv_id = inv_id_merged_val 
																AND newInv_studyName_val = study_name ; 

SELECT amount_merged_val, inv_id_merged_val, "dato", inv_evidences, same_study_in;
									IF amount_merged_val = inv_evidences AND same_study_in = 0  THEN
										

select inv_evidences, amount_merged_val, same_study_in, "1";												

										INSERT INTO merge_study_to_inv	(inv_id, study_name, study_id, chr, bp1s, bp1e, bp2s, bp2e)
											VALUES (inv_id_merged_val, newInv_studyName_val, newInv_id_val, newInv_chr_val, newInv_bp1s_val, newInv_bp1e_val,  newInv_bp2s_val, newInv_bp2e_val);	
										UPDATE inv_main SET detected_amount = detected_amount+1 WHERE id = inv_id_merged_val;	
								
									ELSEIF amount_merged_val = inv_evidences AND same_study_in != 0 THEN
											
										

select inv_evidences, amount_merged_val, same_study_in, "2";												

									INSERT INTO inv_main	(chr, range_start, range_end, size, detected_amount) VALUES (newInv_chr_val, newInv_bp1s_val, newInv_bp2e_val, newInv_bp2e_val - newInv_bp1s_val, inv_evidences);
									SELECT LAST_INSERT_ID() INTO newInv_main_id_val;
									INSERT INTO merge_study_to_inv	(inv_id, study_name, study_id, chr, bp1s, bp1e, bp2s, bp2e)
										VALUES (newInv_main_id_val, newInv_studyName_val, newInv_id_val, newInv_chr_val, newInv_bp1s_val, newInv_bp1e_val,  newInv_bp2s_val, newInv_bp2e_val);
									INSERT INTO merge_study_to_inv	(inv_id, study_name, study_id, chr, bp1s, bp1e, bp2s, bp2e)
									SELECT newInv_main_id_val, study_name, study_id, chr, bp1s, bp1e, bp2s, bp2e FROM merge_study_to_inv
									WHERE inv_id = inv_id_merged_val
											AND study_name != newInv_studyName_val; 
									
									ELSE
									

select inv_evidences, amount_merged_val, same_study_in, "3";												

									INSERT INTO inv_main	(chr, range_start, range_end, size, detected_amount) VALUES (newInv_chr_val, newInv_bp1s_val, newInv_bp2e_val, newInv_bp2e_val - newInv_bp1s_val, amount_merged_val+1);
									SELECT LAST_INSERT_ID() INTO newInv_main_id_val;
									INSERT INTO merge_study_to_inv	(inv_id, study_name, study_id, chr, bp1s, bp1e, bp2s, bp2e)
										VALUES (newInv_main_id_val, newInv_studyName_val, newInv_id_val, newInv_chr_val, newInv_bp1s_val, newInv_bp1e_val,  newInv_bp2s_val, newInv_bp2e_val);
									INSERT INTO merge_study_to_inv	(inv_id, study_name, study_id, chr, bp1s, bp1e, bp2s, bp2e)
									SELECT newInv_main_id_val, study_name, study_id, chr, bp1s, bp1e, bp2s, bp2e FROM merge_study_to_inv
									WHERE(
																	( newInv_bp1s_val BETWEEN bp1s AND bp1e) 
																	OR 
																	( newInv_bp1e_val BETWEEN bp1s AND bp1e )
																	OR
																	( (newInv_bp1s_val < bp1s)  AND (newInv_bp1e_val > bp1e) )
																) 
																AND(
																	( newInv_bp2s_val BETWEEN bp2s AND bp2e) 
																	OR 
																	( newInv_bp2e_val BETWEEN bp2s AND bp2e )
																	OR
																	( (newInv_bp2s_val < bp2s) AND (newInv_bp2e_val > bp2e))
																)	
																AND newInv_bp1e_val <  bp2s
																AND newInv_bp2s_val >  bp1e  
																AND newInv_chr_val = chr
																AND inv_id = inv_id_merged_val
																AND study_name != newInv_studyName_val; 
			
									END IF ; 

									SET loop_cntr_merged = loop_cntr_merged + 1; 
							END WHILE; 	
							
			

					END IF;


					



	




  
  
  select loop_cntr_merged;

END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP PROCEDURE IF EXISTS `translate_database` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8 */ ;
/*!50003 SET character_set_results = utf8 */ ;
/*!50003 SET collation_connection  = utf8_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = '' */ ;
DELIMITER ;;
CREATE DEFINER=`invfest_admin`@`localhost` PROCEDURE `translate_database`( IN previous_assembly VARCHAR(255), IN assembly_val VARCHAR(255),  IN steps VARCHAR(255), IN user_id INT, OUT split_message VARCHAR(255))
    SQL SECURITY INVOKER
BEGIN
 
# VARIABLES

	DECLARE prediction_id INT;
	DECLARE found_rows INT DEFAULT 0; 
	DECLARE loop_cntr INT DEFAULT 0; 
	DECLARE case_complete INT DEFAULT 0;
	DECLARE bp1s_lifted INT DEFAULT 0;  
	DECLARE bp1e_lifted INT DEFAULT 0;
	DECLARE bp2s_lifted INT DEFAULT 0;
	DECLARE bp2e_lifted INT DEFAULT 0;
	DECLARE comment_lifted VARCHAR(255);
	DECLARE prediction_error_val INT;
	DECLARE rbp1s_lifted INT; 
	DECLARE rbp1e_lifted INT;
	DECLARE rbp2s_lifted INT;
	DECLARE rbp2e_lifted INT;
	#DECLARE previous_assembly VARCHAR(255); 
	DECLARE	 inversion_id INT;
	DECLARE num_preds INT;
	DECLARE num_original INT;
	DECLARE num_rows INT;
	DECLARE row_cntr INT DEFAULT 0;
	DECLARE comp INT DEFAULT 0;
	DECLARE comm_value TEXT;
	DECLARE stat_value VARCHAR(255);
	DECLARE chr_val VARCHAR(255); 
	DECLARE RBP1s_val INT;
	DECLARE RBP1e_val INT;
	DECLARE RBP2s_val INT;
	DECLARE RBP2e_val INT;
	DECLARE amount_merged_pred INT DEFAULT 0;
	DECLARE previous_value_val text;
	DECLARE newer_value_val text;
	DECLARE task_val text;
	DECLARE manual_curations VARCHAR(255);
	DECLARE comm_id INT;
	DECLARE username VARCHAR(255);
    DECLARE exist_assembly INT DEFAULT 0;
    DECLARE correction INT;
    DECLARE prev_comm LONGTEXT; 
    DECLARE inv_list VARCHAR(255);
	DECLARE num_rows_merged INT DEFAULT 0; 
    DECLARE newinv INT;
    DECLARE newinv_list VARCHAR(255);
	DECLARE summary_message VARCHAR (255);
    DECLARE split_message_list VARCHAR (255);
	DECLARE description_val  TEXT;
    
    
# CURSORS
  DECLARE prediction_cur CURSOR FOR
    SELECT id
    FROM predictions;

  DECLARE inversion_cur CURSOR FOR
    SELECT id
    FROM inversions;

  DECLARE predi_cur_new CURSOR FOR
		SELECT DISTINCT chr, RBP1s, RBP1e, RBP2s, RBP2e
		FROM predictions
		WHERE inv_id = inversion_id AND (  accuracy NOT LIKE ('%unlifted%') OR accuracy IS NULL)
		GROUP BY research_name, research_id;


### START ###

# ONLY WORKS if the assembly is valid
SELECT COUNT(*) INTO exist_assembly FROM coordinates_lifted WHERE assembly = assembly_val;

IF exist_assembly >0 THEN
	
	# Store last assembly
	#SELECT Column_Default
	#	INTO previous_assembly
 	#	FROM Information_Schema.Columns
	#	WHERE Table_Schema = 'INVFEST-DB-dev'    AND
 	#		Table_Name = 'breakpoints'  AND 
 	#		Column_Name = 'assembly';        
 
	 SELECT CONCAT(previous_assembly, ' to ', assembly_val);
     
	# Make query and change default assembly in breakpoints table
	SET @quer = CONCAT("ALTER TABLE `INVFEST-DB-dev`.`breakpoints` ALTER COLUMN `assembly` SET DEFAULT '", assembly_val, "'");
	PREPARE STMT FROM @quer;
	EXECUTE STMT;
    
    # Count predictions for the loop
    IF steps REGEXP 1 THEN
		OPEN prediction_cur;
			SELECT FOUND_ROWS() INTO found_rows;
		CLOSE prediction_cur;
	ELSE
		SET found_rows =0;
	END IF;
	# FOREACH PREDICTION
	OPEN prediction_cur;
		WHILE loop_cntr < found_rows DO

			FETCH prediction_cur
				INTO  prediction_id;
			
			# READ coordinates_lifted
			SELECT bp1s, bp1e, bp2s, bp2e, comments
				INTO bp1s_lifted ,bp1e_lifted,bp2s_lifted , bp2e_lifted, comment_lifted 
                FROM coordinates_lifted 
                WHERE table_id = prediction_id AND assembly = assembly_val AND table_ref = 'predictions'; 
                
			# if complete assembly { prediction table updated + rbps}
			IF (bp1s_lifted IS NOT NULL AND bp1e_lifted IS NOT NULL AND bp2s_lifted IS NOT NULL AND bp2e_lifted IS NOT NULL) THEN
		
				UPDATE predictions SET BP1s = bp1s_lifted , BP1e = bp1e_lifted, BP2s= bp2s_lifted, BP2e =bp2e_lifted  WHERE id = prediction_id;
			
				# If it was tagged as FILTERED_liftover it has to be corrected
				SELECT COUNT(*) INTO correction FROM predictions WHERE id = prediction_id AND status = 'FILTERED_liftover';
               
                IF correction > 0 THEN
					UPDATE predictions SET status = NULL WHERE id = prediction_id;
                END IF;
				
                # there were comments in the accuracy it has to be corrected (if it is outside the inversion breakpoints it will be tagged again)
				SELECT COUNT(*) INTO correction FROM predictions WHERE id = prediction_id AND accuracy LIKE '%unlifted%';
               
                IF correction > 0 THEN
					UPDATE predictions SET accuracy = NULL WHERE id = prediction_id;
                END IF;
        
				# Save confidence interval
				SELECT prediction_error INTO prediction_error_val
					FROM researchs r INNER JOIN predictions p 
					WHERE p.research_name = r.name AND p.id = prediction_id;
	
				# Apply confidence interval to coordinates when breakpoints are narrower than the confidence interval
				
				IF (bp1e_lifted -   bp1s_lifted  < prediction_error_val)  THEN
					SET rbp1s_lifted = bp1e_lifted - prediction_error_val;
					SET rbp1e_lifted = bp1s_lifted + prediction_error_val;
				ELSE
					SET rbp1s_lifted = bp1s_lifted;
					SET rbp1e_lifted = bp1e_lifted;
				END IF ;
				IF (bp2e_lifted -  bp2s_lifted < prediction_error_val)  THEN
					SET rbp2s_lifted = bp2e_lifted - prediction_error_val;
					SET rbp2e_lifted = bp2s_lifted + prediction_error_val; 
				ELSE
					SET rbp2s_lifted = bp2s_lifted;
					SET rbp2e_lifted = bp2e_lifted;
				END IF ;
	
				UPDATE predictions SET RBP1s = rbp1s_lifted , RBP1e = rbp1e_lifted, RBP2s= rbp2s_lifted, RBP2e = rbp2e_lifted  WHERE id = prediction_id;
				
			# else {prediction table untouched, accuracy set to "Unable to convert hg18 coordinates into [new assembly], not taken into account when calculating inversion breakpoints" , status FILTERED"
			ELSE
				UPDATE predictions
					SET accuracy = CONCAT('Coordinates in ',previous_assembly, ', unlifted. This prediction is not taken into account when estimating inversion breakpoints.') , 
						status = 'FILTERED_liftover'
					WHERE id = prediction_id;

			END IF;

			# The comments are always updated
            SELECT comments into prev_comm FROM predictions WHERE id = prediction_id;

			UPDATE predictions 
				SET comments = CONCAT( IFNULL(comment_lifted, ''),' ', IFNULL(prev_comm, ''))
                WHERE id = prediction_id;
			
            # Continue loop
			SET loop_cntr = loop_cntr + 1; 
			SET case_complete = 0;

		END WHILE;
	SET summary_message = 'predictions completed. ';
    SELECT CONCAT(summary_message);
	CLOSE prediction_cur;
	SET loop_cntr = 0;

	# Count inversions for the loop
    IF steps REGEXP 2 THEN
		OPEN inversion_cur;
			SELECT FOUND_ROWS() INTO found_rows;
		CLOSE inversion_cur;
	ELSE
		SET found_rows =0;
	END IF;


	# FOREACH INVERSION
	OPEN inversion_cur;
		WHILE loop_cntr < found_rows DO

			FETCH inversion_cur
				INTO  inversion_id;
                
			# Find inversions without predictions
			SELECT COUNT(id) into num_preds
				FROM predictions
				where inv_id = inversion_id AND (  accuracy NOT LIKE ('%unlifted%') OR accuracy IS NULL);
			
			# IF no predictions are left {inversion set to FILTERED OUT, predictions set to FILTERED, NULL coordinates}
			IF num_preds < 1 then
				SELECT comment into comm_value FROM inversions WHERE id = inversion_id;
				SELECT status into stat_value FROM inversions WHERE id = inversion_id;
				UPDATE inversions i 
					SET i.status = 'withdrawn', i.comment = CONCAT('Unable to convert ',previous_assembly,' coordinates to ',assembly_val,'. ', 
						(CASE
							WHEN stat_value LIKE 'FALSE' THEN 'The previous status was "False". '
							WHEN stat_value LIKE 'FILTERED OUT' THEN 'The previous status was "Unreliable prediction". '
							WHEN stat_value LIKE 'withdrawn' THEN 'The previous status was "Obsolete". '
							WHEN stat_value LIKE 'TRUE' THEN 'The previous status was "Validated". '
							WHEN stat_value LIKE 'Ambiguous' THEN 'The previous status was "Ambiguous". '
							WHEN stat_value LIKE 'ND' THEN 'The previous status was "Predicted". '		
							WHEN stat_value IS NULL THEN 'The previous status was "Predicted". '
							ELSE ""
						END ),
						(CASE
							WHEN comm_value IS NULL OR comm_value LIKE '%NULL%'THEN ""
							ELSE comm_value
						END) )
						where i.id = inversion_id;
				select comment_id, inversion_com into comm_id, comm_value from comments where inv_id = inversion_id ORDER BY comment_id DESC LIMIT 1;
				SELECT user INTO username FROM user WHERE id = user_id;
				INSERT INTO comments ( inv_id, user, date, inversion_com)
					VALUES ( inversion_id, username ,CURDATE(), CONCAT('Unable to convert ',previous_assembly,' coordinates to ',assembly_val,'. ', 
						(CASE
							WHEN stat_value LIKE 'FALSE' THEN 'The previous status was "False". '
							WHEN stat_value LIKE 'FILTERED OUT' THEN 'The previous status was "Unreliable prediction". '
							WHEN stat_value LIKE 'withdrawn' THEN 'The previous status was "Obsolete". '
                            WHEN stat_value LIKE 'TRUE' THEN 'The previous status was "Validated". '
                            WHEN stat_value LIKE 'Ambiguous' THEN 'The previous status was "Ambiguous". '
                            WHEN stat_value LIKE 'ND' THEN 'The previous status was "Predicted". '		
                            WHEN stat_value IS NULL THEN 'The previous status was "Predicted". '
							ELSE ""
						END ),
                        (CASE
							WHEN comm_value IS NULL OR comm_value LIKE '%NULL%'THEN ""
							ELSE comm_value
						END) ));
				SET comm_value = "";
		
		 
			
			ELSE
            
				# Empty counters
				SET bp1s_lifted = NULL;
				SET bp1e_lifted = NULL;
				SET bp2s_lifted = NULL;
				SET bp2e_lifted = NULL;
			
				# READ coordinates_lifted to find manual curations
				SELECT bp1s, bp1e, bp2s, bp2e, chr, comments
					INTO bp1s_lifted ,bp1e_lifted,bp2s_lifted , bp2e_lifted, chr_val, comment_lifted 
					FROM coordinates_lifted 
					WHERE table_id = inversion_id AND assembly = assembly_val AND table_ref = 'breakpoints'; 
				   
				# if complete manual curation { add manual curation}
				IF (bp1s_lifted IS NOT NULL AND bp1e_lifted IS NOT NULL AND bp2s_lifted IS NOT NULL AND bp2e_lifted IS NOT NULL) THEN
					
					SET description_val = CONCAT('Coordinate conversion from ',previous_assembly,' to ',assembly_val,'. ' , IFNULL(comment_lifted , ''));
                    
                    CALL add_BP (NULL,inversion_id, chr_val, bp1s_lifted, bp1e_lifted, bp2s_lifted,bp2e_lifted, description_val, user_id);

				ELSE	
					SET description_val = CONCAT('Coordinate conversion from ',previous_assembly,' to ',assembly_val,'.');
					
                    CALL update_BP(inversion_id, description_val, 'TRANSLATE', user_id);	
			
                END IF;
			END IF;
			# Continue loop
			SET loop_cntr = loop_cntr + 1; 
			SET case_complete = 0;
		
		END WHILE;
	SELECT CONCAT('inversions completed. ');

    SET loop_cntr = 0;
	CLOSE inversion_cur;
    
    # Find bridge predictions
    # If the prediction is outside the current inversion breakpoints it won't join the inversions... 
    
    IF steps REGEXP 3 THEN
		OPEN inversion_cur;
			SELECT FOUND_ROWS() INTO found_rows;
		CLOSE inversion_cur;
	ELSE
		SET found_rows =0;
	END IF;


	# FOREACH PREDICTION
	OPEN inversion_cur;
		WHILE loop_cntr < found_rows DO

			FETCH inversion_cur
				INTO  inversion_id;
            
            SELECT status INTO stat_value FROM inversions WHERE id = inversion_id;
            
            IF stat_value != 'withdrawn' THEN
				CALL find_bridge_prediction(inversion_id, NULL,NULL, NULL, NULL, NULL, inv_list, num_rows_merged );
				#SELECT CONCAT_WS(', ',prediction_id, inv_list, num_rows_merged )	;
				IF num_rows_merged > 1 THEN 	
					
				   CALL merge_inversions(inv_list, NULL, inv_list, NULL, NULL, NULL, NULL, NULL, NULL, inv_list, NULL, user_id, newinv);
					# Merge inversions adjustes the detected amount
				   #SET newinv_list = CONCAT_WS(newinv_list, newinv);
				   #SELECT CONCAT(newinv_list);
					SELECT GROUP_CONCAT(i.name SEPARATOR ', ') 
						INTO split_message_list
						FROM inversion_history h
						INNER JOIN inversions i ON h.new_inv_id = i.id 
						WHERE h.cause LIKE '%split%' AND 
							FIND_IN_SET(h.new_inv_id, inv_list)>0;
					SET split_message = CONCAT_WS(split_message ,IFNULL(split_message_list, ''));
					# Update BP is called by merge_inversions
		
				END IF;			
			END IF;
            # Continue loop
			SET loop_cntr = loop_cntr + 1; 
			SET case_complete = 0;

		END WHILE;
	SET summary_message = CONCAT('bridges completed. ');

	CLOSE inversion_cur;
	SET loop_cntr = 0;
    
    # At the end, review complexity, not taking into account accuracy problematic predictions
	CALL revise_complexity(NULL, user_id);

	SELECT CONCAT(summary_message);
# and  bridge	PREDICTIONS
ELSE
	SELECT CONCAT('Your assembly does not exist');
    
END IF;
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP PROCEDURE IF EXISTS `update_BP` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8 */ ;
/*!50003 SET character_set_results = utf8 */ ;
/*!50003 SET collation_connection  = utf8_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = '' */ ;
DELIMITER ;;
CREATE DEFINER=`invfest_admin`@`localhost` PROCEDURE `update_BP`(IN `inversion_id_val` int, IN description_val VARCHAR(255),IN origin VARCHAR(255), IN user_id_val INT)
    SQL SECURITY INVOKER
BEGIN
	 
 
### DECLARE VARIABLES
 
  DECLARE inv_BP1s_val INT DEFAULT NULL;
  DECLARE inv_BP1e_val INT DEFAULT NULL;
  DECLARE inv_BP2s_val INT DEFAULT NULL;
  DECLARE inv_BP2e_val INT DEFAULT NULL;
  DECLARE inv_chr_val VARCHAR(255);
  
  DECLARE current_assembly VARCHAR(255);
  DECLARE new_BP_id_val  INT DEFAULT NULL;
  
  DECLARE no_more_rows_inversion BOOLEAN;
  DECLARE num_rows_inversion INT DEFAULT 0;
  DECLARE loop_cntr_inversion INT DEFAULT 0;
  
  DECLARE inv_id_val INT;
  DECLARE research_name_val VARCHAR(255);
  DECLARE pred_id_val INT;
  DECLARE chr_val VARCHAR(255);
  DECLARE BP1s_val INT;
  DECLARE BP1e_val INT;
  DECLARE BP2s_val INT;
  DECLARE BP2e_val INT;
  
  DECLARE next_date  DATE ;
  DECLARE previous_value_val text;
  DECLARE newer_value_val text;
  DECLARE task_val text;
  DECLARE using_cursor INT DEFAULT 1;

  
	
### DECLARE CURSORS


# Fetches all predictions - unless they are unlifted or filtered out - for a given inversion

  DECLARE inversion_cur CURSOR FOR	
	SELECT inv_id, research_name, research_id, chr, 
		IF(  research_name = "Martinez-Fundichely et al. 2013", BP1s,  RBP1s), 
		IF(  research_name = "Martinez-Fundichely et al. 2013", BP1e,  RBP1e), 
		IF(  research_name = "Martinez-Fundichely et al. 2013", BP2s,  RBP2s), 
		IF(  research_name = "Martinez-Fundichely et al. 2013", BP2e,  RBP2e)
		FROM predictions
		WHERE inv_id = inversion_id_val 
			AND (  accuracy NOT LIKE ('%unlifted%') OR accuracy IS NULL)
			AND (status NOT LIKE ('%FILTERED%') OR status IS NULL);
            
  DECLARE filter_cur CURSOR FOR	
	SELECT inv_id, research_name, research_id, chr, 
		IF(  research_name = "Martinez-Fundichely et al. 2013", BP1s,  RBP1s), 
		IF(  research_name = "Martinez-Fundichely et al. 2013", BP1e,  RBP1e), 
		IF(  research_name = "Martinez-Fundichely et al. 2013", BP2s,  RBP2s), 
		IF(  research_name = "Martinez-Fundichely et al. 2013", BP2e,  RBP2e)
		FROM predictions
		WHERE inv_id = inversion_id_val 
			AND (  accuracy NOT LIKE ('%unlifted%') OR accuracy IS NULL)
			AND (status  LIKE ('FILTERED') OR status  LIKE ('on_FILTERED OUT'));  # status like on_FILTERED OUT or FILTERED but not FILTERED_liftover

# Continue handler
	DECLARE CONTINUE HANDLER FOR NOT FOUND
    SET no_more_rows_inversion = TRUE;

# START PROCESS
	
# First the correct origin will be checked (this is to avoid further iterations if the procedure was caller incorrectly)
              
 IF origin IN ('MERGE', 'SPLIT' , 'PREDICTION', 'VALIDATION', 'TRANSLATE') THEN
 
 	# When it is a merge or split
     IF origin IN ('MERGE') THEN
 		SET description_val = CONCAT('Unrefined: Result of inversion merging. ',description_val);
 		SELECT	MIN(RBP1s), MAX(RBP1e), MIN(RBP2s), MAX(RBP2e),	chr
 			INTO inv_BP1s_val, inv_BP1e_val, inv_BP2s_val, inv_BP2e_val, inv_chr_val
 			FROM predictions
 			WHERE inv_id = inversion_id_val 
 				AND(  accuracy NOT LIKE ('%unlifted%') OR accuracy IS NULL) 
 				AND (status NOT LIKE ('%FILTERED%') OR status IS NULL)
 			group by chr;
		IF inv_chr_val IS NULL THEN # there were no unfiltered results
				SELECT	MIN(RBP1s), MAX(RBP1e), MIN(RBP2s), MAX(RBP2e),	chr
 			INTO inv_BP1s_val, inv_BP1e_val, inv_BP2s_val, inv_BP2e_val, inv_chr_val
 			FROM predictions
 			WHERE inv_id = inversion_id_val 
 				AND(  accuracy NOT LIKE ('%unlifted%') OR accuracy IS NULL) 
 				AND (status NOT LIKE ('FILTERED_liftover') OR status IS NULL)
 			group by chr;
		END IF;
        
     END IF;
 	IF origin IN ('SPLIT') THEN
 		SET description_val = CONCAT('Unrefined: Result of inversion splitting. ',description_val);
 		SELECT	MIN(RBP1s), MAX(RBP1e), MIN(RBP2s), MAX(RBP2e),	chr
 			INTO inv_BP1s_val, inv_BP1e_val, inv_BP2s_val, inv_BP2e_val, inv_chr_val
 			FROM predictions
 			WHERE inv_id = inversion_id_val 
 				AND(  accuracy NOT LIKE ('%unlifted%') OR accuracy IS NULL) 
 				AND (status NOT LIKE ('%FILTERED%') OR status IS NULL)
			group by chr;	
		IF inv_chr_val IS NULL THEN # there were no unfiltered results
			SELECT	MIN(RBP1s), MAX(RBP1e), MIN(RBP2s), MAX(RBP2e),	chr
 			INTO inv_BP1s_val, inv_BP1e_val, inv_BP2s_val, inv_BP2e_val, inv_chr_val
 			FROM predictions
 			WHERE inv_id = inversion_id_val 
 				AND(  accuracy NOT LIKE ('%unlifted%') OR accuracy IS NULL) 
 				AND (status NOT LIKE ('FILTERED_liftover') OR status IS NULL)
 			group by chr;
		END IF;
     END IF;
     
   
     IF origin IN ('PREDICTION', 'VALIDATION', 'TRANSLATE')THEN
 		# SEARCH FOR A MANUAL CURATION
    		
		#DROP TEMPORARY TABLE IF EXISTS last_bp_output ;
 		CALL get_last_bp(NULL);


 		SELECT id, chr,  bp1_start, bp1_end, bp2_start, bp2_end 
		INTO new_BP_id_val, inv_chr_val, inv_BP1s_val, inv_BP1e_val, inv_BP2s_val, inv_BP2e_val
 			FROM last_bp_output 
 			WHERE inv_id = inversion_id_val AND
 				definition_method = 'manual curation' ;
 				
         # NO MANUAL CURATION: minimum region
         # Si alguna cosa del manual curation esta incompleta = no manual curation
		IF inv_BP1s_val IS NULL OR inv_BP1e_val IS NULL OR inv_BP2s_val IS NULL OR  inv_BP2e_val IS NULL  OR  inv_chr_val IS NULL THEN
        
 			OPEN inversion_cur;
 			SELECT FOUND_ROWS() INTO num_rows_inversion;
            CLOSE inversion_cur;
            SET using_cursor = 1;
            
            IF (num_rows_inversion = 0) THEN # no predictions withoud filtered, then all the filtered predictions are taken into account
				OPEN filter_cur;
				SELECT FOUND_ROWS() INTO num_rows_inversion;
				CLOSE filter_cur;
                SET using_cursor = 2;
            END IF;
   
			IF using_cursor < 2 THEN
				OPEN inversion_cur;
            ELSE
				OPEN filter_cur;
            END IF;
            
			WHILE loop_cntr_inversion < num_rows_inversion DO 
 				
				IF using_cursor < 2 THEN
					FETCH  inversion_cur # a inversion_cur ja estan seleccionades només les prediccions bones (que s'han traduit be i no estan filtered)
						INTO	inv_id_val, research_name_val, pred_id_val, inv_chr_val, BP1s_val, BP1e_val, BP2s_val, BP2e_val;
				ELSE
					FETCH  filter_cur # a inversion_cur ja estan seleccionades només les prediccions bones (que s'han traduit be i no estan filtered)
						INTO	inv_id_val, research_name_val, pred_id_val, inv_chr_val, BP1s_val, BP1e_val, BP2s_val, BP2e_val;
				END IF;
                
				# Si el manual curation no existia
                 IF  inv_BP1s_val IS NULL AND inv_BP1e_val IS NULL AND inv_BP2s_val IS NULL AND  inv_BP2e_val IS NULL  THEN
 					# SET SEED
 					SET inv_BP1s_val  = BP1s_val;
 					SET inv_BP1e_val  = BP1e_val;
 					SET inv_BP2s_val  = BP2s_val;
 					SET inv_BP2e_val  = BP2e_val;
 				ELSE # Si el manual curation esta començat
 					# CONTINUE SEED
                     IF 	( BP1s_val BETWEEN inv_BP1s_val AND  inv_BP1e_val)  THEN
 						SET inv_BP1s_val  = BP1s_val;
 					END IF;
 					IF   ( BP1e_val BETWEEN inv_BP1s_val AND  inv_BP1e_val )  THEN
 						SET inv_BP1e_val  = BP1e_val;
 					END IF	;
 					IF 		(BP1e_val <= inv_BP1s_val  )  OR  (BP1s_val >=  inv_BP1e_val)  THEN
 						IF ( BP1e_val <=  inv_BP1s_val) THEN
 							SET inv_BP1s_val  = BP1s_val ;
 						ELSE
 							SET inv_BP1e_val  = BP1e_val;
 						END IF;
 					END IF;
                     IF 	( BP2s_val BETWEEN inv_BP2s_val AND  inv_BP2e_val)  THEN
 						SET inv_BP2s_val  = BP2s_val;
 					END IF;
 					IF   ( BP2e_val BETWEEN inv_BP2s_val AND  inv_BP2e_val )  THEN
 						SET inv_BP2e_val  = BP2e_val;
 					END IF	;
 					IF 	( (BP2e_val <= inv_BP2s_val  )  OR  ( BP2s_val >=  inv_BP2e_val) ) THEN
 						IF ( BP2e_val <=  inv_BP2s_val) THEN
 							SET inv_BP2s_val  = BP2s_val ;
 						ELSE
 							SET inv_BP2e_val  = BP2e_val;
 						END IF;
 					END IF;
 				END IF;
 				SET loop_cntr_inversion = loop_cntr_inversion + 1; 									
 			END WHILE ;
			
            IF using_cursor < 2 THEN
				CLOSE inversion_cur;
            ELSE
				CLOSE filter_cur;
            END IF;
         
         # Si alguna de les regions encara no esta completa
			IF inv_BP1s_val IS NULL OR inv_BP1e_val IS NULL OR inv_BP2s_val IS NULL OR  inv_BP2e_val IS NULL OR  inv_chr_val IS NULL THEN
				SET description_val = CONCAT('Breakpoints not refined due to lack of overlap of predictions in at least one breakpoint. ',description_val);
				 SELECT	MIN(RBP1s), MAX(RBP1e), MIN(RBP2s), MAX(RBP2e),	chr
					INTO inv_BP1s_val, inv_BP1e_val, inv_BP2s_val, inv_BP2e_val, inv_chr_val
					FROM predictions
					WHERE inv_id = inversion_id_val 
						AND(  accuracy NOT LIKE ('%unlifted%') OR accuracy IS NULL) 
						AND (status NOT LIKE ('%FILTERED%') OR status IS NULL)
					group by chr;		
			END IF; 
         END IF;
     END IF;
     
     ## At the end of this we want the inv_BPx values, the description_val and sometimes the breakpoint ID 
     
     # ADD BREAKPOINT IF NEEDED
 	IF new_BP_id_val IS NULL THEN
 	#	SET next_date = CURRENT_TIMESTAMP();
 		INSERT INTO breakpoints	(inv_id, chr, bp1_start, bp1_end, bp2_start, bp2_end, definition_method, description, date, researcher)
 			VALUES (inversion_id_val, inv_chr_val, inv_BP1s_val, inv_BP1e_val,  inv_BP2s_val, inv_BP2e_val, 'default informatic definition', description_val, CURRENT_TIMESTAMP(), "InvFEST_engine");	
 		SELECT LAST_INSERT_ID() INTO new_BP_id_val;
 		SET task_val = CONCAT('INSERT new breakpoints of inv ',inversion_id_val);
 		CALL  save_log(user_id_val, task_val, "none", new_BP_id_val);
 		
         # ADD extra info
 		CALL  get_inv_gene_realtion(new_BP_id_val);
 		CALL  get_SD_in_BP (new_BP_id_val);
     END IF;
 
 	# ADJUST RANGE
 	SELECT CONCAT('range_start: ',range_start,', range_end: ', range_end,', size: ', size) 
 		INTO previous_value_val
         FROM inversions 
         WHERE id = inversion_id_val;
 	UPDATE inversions 
 		SET range_start = inv_BP1s_val, range_end =  inv_BP2e_val, size = CASE WHEN ((inv_BP2s_val -  inv_BP1e_val)+1)<0 THEN 0 ELSE  (inv_BP2s_val -  inv_BP1e_val)+1 END
 		WHERE id = inversion_id_val;	
 	SELECT CONCAT('range_start: ',range_start,', range_end: ', range_end,', size: ', size) 
 		INTO newer_value_val 
         FROM inversions 
         WHERE id = inversion_id_val;
         
 	IF previous_value_val <> newer_value_val THEN # si els dos missatges son diferents...
 		SET task_val = CONCAT('UPDATE range and size of inv ',inversion_id_val);
 		CALL  save_log(user_id_val, task_val, previous_value_val, newer_value_val);
     END IF;
 
 	# ADJUST ACCURACIES for the current inversion breakpoints
     UPDATE predictions 
     SET accuracy = "prediction outside of the current location of the inversion breakpoints"
		WHERE chr = inv_chr_val  
			AND inv_id = inversion_id_val
			AND ( CASE research_name 
 			WHEN "Martinez-Fundichely et al. 2013" THEN 
 				NOT (( inv_BP1s_val BETWEEN BP1s AND BP1e) 
 					OR ( inv_BP1e_val BETWEEN BP1s AND BP1e )
 					OR ( (inv_BP1s_val <= BP1s)  AND (inv_BP1e_val >= BP1e) )) 
                 OR NOT(( inv_BP2s_val BETWEEN BP2s AND BP2e) 
 					OR ( inv_BP2e_val BETWEEN BP2s AND BP2e )
 					OR ( (inv_BP2s_val <= BP2s) AND (inv_BP2e_val >= BP2e)))
                 ELSE NOT (( inv_BP1s_val BETWEEN RBP1s AND RBP1e) 
 					OR ( inv_BP1e_val BETWEEN RBP1s AND RBP1e )
 					OR ( (inv_BP1s_val <= RBP1s)  AND (inv_BP1e_val >= RBP1e) )) 
                 OR NOT((inv_BP2s_val BETWEEN RBP2s AND RBP2e) 
 					OR ( inv_BP2e_val BETWEEN RBP2s AND RBP2e )
 					OR ( (inv_BP2s_val <= RBP2s) AND (inv_BP2e_val >= RBP2e)))
 			END);

 	UPDATE predictions 
     SET accuracy = NULL 
     WHERE	chr = inv_chr_val  
		AND inv_id = inversion_id_val 
        AND accuracy = 'prediction outside of the current location of the inversion breakpoints'
 		AND ( CASE research_name 
 			WHEN "Martinez-Fundichely et al. 2013" THEN 
 				(( inv_BP1s_val BETWEEN BP1s AND BP1e) 
 					OR ( inv_BP1e_val BETWEEN BP1s AND BP1e )
 					OR ( (inv_BP1s_val <= BP1s)  AND (inv_BP1e_val >= BP1e) )) 
 				AND (( inv_BP2s_val BETWEEN BP2s AND BP2e) 
 					OR ( inv_BP2e_val BETWEEN BP2s AND BP2e )
 					OR ( (inv_BP2s_val <= BP2s) AND (inv_BP2e_val >= BP2e)))
 				ELSE (( inv_BP1s_val BETWEEN RBP1s AND RBP1e) 
 					OR ( inv_BP1e_val BETWEEN RBP1s AND RBP1e )
 					OR ( (inv_BP1s_val <= RBP1s)  AND (inv_BP1e_val >= RBP1e) )) 
 				AND ((inv_BP2s_val BETWEEN RBP2s AND RBP2e) 
					OR ( inv_BP2e_val BETWEEN RBP2s AND RBP2e ) 
 					OR ( (inv_BP2s_val <= RBP2s) AND (inv_BP2e_val >= RBP2e)))
             END );

	#  SELECT CONCAT_WS(', ', current_assembly, inv_BP1s_val, inv_BP1e_val, inv_BP2s_val, inv_BP2e_val, inv_chr_val);							
ELSE

	SELECT CONCAT("Incorrect origin, please specify 'MERGE', 'SPLIT', 'PREDICTION', 'VALIDATION' or 'TRANSLATE'");

END IF;
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP PROCEDURE IF EXISTS `update_BP_newmerge` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8 */ ;
/*!50003 SET character_set_results = utf8 */ ;
/*!50003 SET collation_connection  = utf8_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = '' */ ;
DELIMITER ;;
CREATE DEFINER=`invfest_admin`@`localhost` PROCEDURE `update_BP_newmerge`(IN `inversion_id_val` int, IN description_val VARCHAR(255), IN user_id_val INT)
    SQL SECURITY INVOKER
BEGIN
	
 
  DECLARE inv_id_val INT;
  DECLARE research_name_val VARCHAR(255);
  DECLARE pred_id_val INT;
  DECLARE chr_val VARCHAR(255);
  DECLARE BP1s_val INT;
  DECLARE BP1e_val INT;
  DECLARE BP2s_val INT;
  DECLARE BP2e_val INT;

	DECLARE amount_pred_id_val  INT DEFAULT 0;
	DECLARE same_BP_val  INT DEFAULT 0;
	DECLARE new_BP_id_val  INT DEFAULT 0;
	DECLARE next_date  DATE ;


  DECLARE inv_BP1s_val INT DEFAULT 0;
  DECLARE inv_BP1e_val INT DEFAULT 0;
  DECLARE inv_BP2s_val INT DEFAULT 0;
  DECLARE inv_BP2e_val INT DEFAULT 0;

  DECLARE inv_BP1s INT DEFAULT 0;
  DECLARE inv_BP1e INT DEFAULT 0;
  DECLARE inv_BP2s INT DEFAULT 0;
  DECLARE inv_BP2e INT DEFAULT 0;

	DECLARE previous_value_val text;
	DECLARE newer_value_val text;
	DECLARE task_val text;

	DECLARE inv_curated INT DEFAULT 0;



  DECLARE no_more_rows_inversion BOOLEAN;
  DECLARE loop_cntr_inversion INT DEFAULT 0;
  DECLARE num_rows_inversion INT DEFAULT 0;

DECLARE definition_method_val VARCHAR(255);


  DECLARE inversion_cur CURSOR FOR
    SELECT
        inv_id, research_name, research_id, chr, 
IF(  research_name = "Martinez-Fundichely et al. 2014 (in preparation)", BP1s,  RBP1s), 
IF(  research_name = "Martinez-Fundichely et al. 2014 (in preparation)", BP1e,  RBP1e), 
IF(  research_name = "Martinez-Fundichely et al. 2014 (in preparation)", BP2s,  RBP2s), 
IF(  research_name = "Martinez-Fundichely et al. 2014 (in preparation)", BP2e,  RBP2e)
    FROM predictions
		WHERE inv_id = inversion_id_val;


	DECLARE CONTINUE HANDLER FOR NOT FOUND
    SET no_more_rows_inversion = TRUE;



SELECT COUNT(*) INTO inv_curated FROM breakpoints WHERE inv_id = inversion_id_val AND definition_method = 'manual curation';

IF description_val  IN ('Result of inversion merging', 'Result of inversion spliting') THEN
SET description_val = CONCAT_WS(', ',"unrefined",description_val);
		SELECT
			MIN(RBP1s), MAX(RBP1e), MIN(RBP2s), MAX(RBP2e),	chr
		INTO inv_BP1s_val, inv_BP1e_val, inv_BP2s_val, inv_BP2e_val, chr_val
    FROM predictions
		WHERE inv_id = inversion_id_val;
ELSE

	OPEN inversion_cur;
	SELECT FOUND_ROWS() INTO num_rows_inversion;
WHILE loop_cntr_inversion < num_rows_inversion DO 

SET amount_pred_id_val  = 0;

									FETCH  inversion_cur
									INTO	inv_id_val, research_name_val, pred_id_val, chr_val, BP1s_val, BP1e_val, BP2s_val, BP2e_val;
 
									SELECT COUNT(*) INTO amount_pred_id_val 
											FROM predictions p JOIN inversions i ON (p.inv_id = i.id)
											WHERE  p.research_id =  pred_id_val AND p.research_name = research_name_val AND (p.status NOT LIKE ('%FILTERED%') OR p.status is null);




									IF amount_pred_id_val = 1  THEN
												IF  inv_BP1s_val = 0 AND inv_BP1e_val = 0 AND inv_BP2s_val = 0 AND  inv_BP2e_val = 0 THEN
															SET inv_BP1s_val  = BP1s_val;
															SET inv_BP1e_val  = BP1e_val;
															SET inv_BP2s_val  = BP2s_val;
															SET inv_BP2e_val  = BP2e_val;
															SET definition_method_val = "manual curation";
											ELSE

															SET definition_method_val = "default informatic definition";
															IF 	( BP1s_val BETWEEN inv_BP1s_val AND  inv_BP1e_val)  THEN
																		SET inv_BP1s_val  = BP1s_val;
															END IF;
															IF   ( BP1e_val BETWEEN inv_BP1s_val AND  inv_BP1e_val )  THEN
																		SET inv_BP1e_val  = BP1e_val;
															END IF	;
															
															IF 		(BP1e_val <= inv_BP1s_val  )  OR  (BP1s_val >=  inv_BP1e_val)  THEN
																			IF ( BP1e_val <=  inv_BP1s_val) THEN
																					SET inv_BP1s_val  = BP1s_val ;
																			ELSE
																					SET inv_BP1e_val  = BP1e_val;
																			END IF;
															END IF;

															IF 	( BP2s_val BETWEEN inv_BP2s_val AND  inv_BP2e_val)  THEN
																		SET inv_BP2s_val  = BP2s_val;
															END IF;
															IF   ( BP2e_val BETWEEN inv_BP2s_val AND  inv_BP2e_val )  THEN
																			SET inv_BP2e_val  = BP2e_val;
															END IF	;
														
															IF 	( (BP2e_val <= inv_BP2s_val  )  OR  ( BP2s_val >=  inv_BP2e_val) ) THEN
																			IF ( BP2e_val <=  inv_BP2s_val) THEN
																					SET inv_BP2s_val  = BP2s_val ;
																			ELSE
																					SET inv_BP2e_val  = BP2e_val;
																			END IF;
															END IF;
									
												END IF;
									END IF;
SET loop_cntr_inversion = loop_cntr_inversion + 1; 									
END WHILE ;

IF (inv_BP1s_val + inv_BP1e_val = 0) OR (inv_BP2s_val + inv_BP2e_val = 0) THEN
SET description_val = CONCAT_WS('. ',"Breakpoints not refined due to lack of overlap of predictions in at least one breakpoint",description_val);
		SELECT
			MIN(RBP1s), MAX(RBP1e), MIN(RBP2s), MAX(RBP2e), chr
			
			
			
			
			
		INTO inv_BP1s_val, inv_BP1e_val, inv_BP2s_val, inv_BP2e_val, chr_val
    FROM predictions
		WHERE inv_id = inversion_id_val;
END IF;

END IF;



	
										SET same_BP_val  = 0;	
						
						
						

										IF same_BP_val = 0 THEN
						
SET next_date = CURRENT_TIMESTAMP();
											INSERT INTO breakpoints	(inv_id, chr, bp1_start, bp1_end, bp2_start, bp2_end, definition_method, description, date, researcher)
												VALUES (inversion_id_val, chr_val, inv_BP1s_val, inv_BP1e_val,  inv_BP2s_val, inv_BP2e_val, definition_method_val, description_val, next_date, "InvFEST_engine");	

										SELECT LAST_INSERT_ID() INTO new_BP_id_val;

									SET task_val = CONCAT('INSERT new breakpoints of inv ',inversion_id_val);
									CALL  save_log(user_id_val, task_val, "none", new_BP_id_val);


										CALL  get_inv_gene_realtion(new_BP_id_val);
										

								IF inv_curated = 0 THEN		

										SELECT CONCAT('range_start: ',range_start,', range_end: ', range_end,', size: ', size) INTO previous_value_val FROM inversions WHERE id = inversion_id_val;

										UPDATE inversions SET range_start = inv_BP1s_val, range_end =  inv_BP2e_val, size = (inv_BP2s_val -  inv_BP1e_val)-1 WHERE id = inversion_id_val;	
											
										SELECT CONCAT('range_start: ',range_start,', range_end: ', range_end,', size: ', size) INTO newer_value_val FROM inversions WHERE id = inversion_id_val;

										SET task_val = CONCAT('UPDATE range and size of inv ',inversion_id_val);

										CALL  save_log(user_id_val, task_val, previous_value_val, newer_value_val);

								END If;

											SELECT b.bp1_start, b.bp1_end, b.bp2_start, b.bp2_end INTO inv_BP1s, inv_BP1e, inv_BP2s, inv_BP2e
														FROM inversions i
																				INNER JOIN breakpoints b ON b.id = (
																																						SELECT id FROM breakpoints b2
																																							WHERE b2.inv_id = i.id
																																							ORDER BY FIELD(b2.definition_method, 'manual curation', 'default informatic definition'), b2.`date` DESC
																																							LIMIT 1
																																						) WHERE i.id = inversion_id_val;


UPDATE predictions SET accuracy = "prediction outside of the current location of the inversion breakpoints"
WHERE	chr = chr_val  AND inv_id = inversion_id_val
											AND (
											CASE research_name 
											WHEN "Martinez-Fundichely et al. 2013" THEN 
														NOT (
																						( inv_BP1s BETWEEN BP1s AND BP1e) 
																						OR 
																						( inv_BP1e BETWEEN BP1s AND BP1e )
																						OR
																						( (inv_BP1s <= BP1s)  AND (inv_BP1e >= BP1e) )
																			) 
											OR NOT(
																						( inv_BP2s BETWEEN BP2s AND BP2e) 
																						OR 
																						( inv_BP2e BETWEEN BP2s AND BP2e )
																						OR
																						( (inv_BP2s <= BP2s) AND (inv_BP2e >= BP2e))
																						
																			)
											ELSE
														NOT (
																						( inv_BP1s BETWEEN RBP1s AND RBP1e) 
																						OR 
																						( inv_BP1e BETWEEN RBP1s AND RBP1e )
																						OR
																						( (inv_BP1s <= RBP1s)  AND (inv_BP1e >= RBP1e) )
																			) 
											OR NOT(
																						(inv_BP2s BETWEEN RBP2s AND RBP2e) 
																						OR 
																						( inv_BP2e BETWEEN RBP2s AND RBP2e )
																						OR
																						( (inv_BP2s <= RBP2s) AND (inv_BP2e >= RBP2e))
																						
																				)
											END
											);

											UPDATE predictions SET accuracy = NULL
WHERE	chr = chr_val  AND inv_id = inversion_id_val AND accuracy = 'prediction outside of the current location of the inversion breakpoints'
											AND (
											CASE research_name 
											WHEN "Martinez-Fundichely et al. 2013" THEN 
																				(
																						( inv_BP1s BETWEEN BP1s AND BP1e) 
																						OR 
																						( inv_BP1e BETWEEN BP1s AND BP1e )
																						OR
																						( (inv_BP1s <= BP1s)  AND (inv_BP1e >= BP1e) )
																				) 
															AND (
																						( inv_BP2s BETWEEN BP2s AND BP2e) 
																						OR 
																						( inv_BP2e BETWEEN BP2s AND BP2e )
																						OR
																						( (inv_BP2s <= BP2s) AND (inv_BP2e >= BP2e))
																						
																					)
												ELSE
																					(
																						( inv_BP1s BETWEEN RBP1s AND RBP1e) 
																						OR 
																						( inv_BP1e BETWEEN RBP1s AND RBP1e )
																						OR
																						( (inv_BP1s <= RBP1s)  AND (inv_BP1e >= RBP1e) )
																					) 
																AND (
																						(inv_BP2s BETWEEN RBP2s AND RBP2e) 
																						OR 
																						( inv_BP2e BETWEEN RBP2s AND RBP2e )
																						OR
																						( (inv_BP2s <= RBP2s) AND (inv_BP2e >= RBP2e))
																					)
												END
												);

										END IF;

END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP PROCEDURE IF EXISTS `update_BP_public_info` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8 */ ;
/*!50003 SET character_set_results = utf8 */ ;
/*!50003 SET collation_connection  = utf8_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = '' */ ;
DELIMITER ;;
CREATE DEFINER=`invfest_admin`@`localhost` PROCEDURE `update_BP_public_info`(in c int)
BEGIN
	
 
  DECLARE inv_id_val INT;
  DECLARE research_name_val VARCHAR(255);
  DECLARE pred_id_val INT;
  DECLARE chr_val VARCHAR(255);
  DECLARE BP1s_val INT;
  DECLARE BP1e_val INT;
  DECLARE BP2s_val INT;
  DECLARE BP2e_val INT;
	DECLARE amount_pred_id_val  INT DEFAULT 0;
	DECLARE same_BP_val  INT DEFAULT 0;
	DECLARE new_BP_id_val  INT DEFAULT 0;
	DECLARE next_date  DATE ;
  DECLARE inv_BP1s_val INT DEFAULT 0;
  DECLARE inv_BP1e_val INT DEFAULT 0;
  DECLARE inv_BP2s_val INT DEFAULT 0;
  DECLARE inv_BP2e_val INT DEFAULT 0;
  DECLARE inv_BP1s INT DEFAULT 0;
  DECLARE inv_BP1e INT DEFAULT 0;
  DECLARE inv_BP2s INT DEFAULT 0;
  DECLARE inv_BP2e INT DEFAULT 0;
	DECLARE previous_value_val TEXT;
	DECLARE newer_value_val TEXT;
	DECLARE task_val TEXT;

  DECLARE no_more_rows_inversion BOOLEAN;
  DECLARE loop_cntr_inversion INT DEFAULT 0;
  DECLARE num_rows_inversion INT DEFAULT 0;
  DECLARE no_more_rows_inversion1 BOOLEAN;
  DECLARE loop_cntr_inversion1 INT DEFAULT 0;
  DECLARE num_rows_inversion1 INT DEFAULT 0;
  DECLARE inversion_id_val INT;
DECLARE definition_method_val VARCHAR(255);

SET inversion_id_val = c;


											SELECT i.chr, b.bp1_start, b.bp1_end, b.bp2_start, bp2_end INTO chr_val, inv_BP1s, inv_BP1e, inv_BP2s, inv_BP2e
														FROM inversions i
																				INNER JOIN breakpoints b ON b.id = (
																																						SELECT id FROM breakpoints b2
																																							WHERE b2.inv_id = i.id
																																							ORDER BY FIELD(b2.definition_method, 'manual curation', 'default informatic definition'), b2.`id` DESC
																																							LIMIT 1
																																						) WHERE i.id = inversion_id_val;
UPDATE predictions SET accuracy = "Prediction outside of the current location of the inversion breakpoints"
WHERE	chr = chr_val  AND inv_id = inversion_id_val
											AND (
											CASE research_name 
											WHEN "Martinez-Fundichely et al. 2013" THEN 
														NOT (
																						( inv_BP1s BETWEEN BP1s AND BP1e) 
																						OR 
																						( inv_BP1e BETWEEN BP1s AND BP1e )
																						OR
																						( (inv_BP1s <= BP1s)  AND (inv_BP1e >= BP1e) )
																			) 
											OR NOT(
																						( inv_BP2s BETWEEN BP2s AND BP2e) 
																						OR 
																						( inv_BP2e BETWEEN BP2s AND BP2e )
																						OR
																						( (inv_BP2s <= BP2s) AND (inv_BP2e >= BP2e))
																						
																			)
											ELSE
														NOT (
																						( inv_BP1s BETWEEN RBP1s AND RBP1e) 
																						OR 
																						( inv_BP1e BETWEEN RBP1s AND RBP1e )
																						OR
																						( (inv_BP1s <= RBP1s)  AND (inv_BP1e >= RBP1e) )
																			) 
											OR NOT(
																						(inv_BP2s BETWEEN RBP2s AND RBP2e) 
																						OR 
																						( inv_BP2e BETWEEN RBP2s AND RBP2e )
																						OR
																						( (inv_BP2s <= RBP2s) AND (inv_BP2e >= RBP2e))
																						
																				)
											END
											);
											UPDATE predictions SET accuracy = NULL
WHERE	chr = chr_val  AND inv_id = inversion_id_val AND accuracy = 'Prediction outside of the current location of the inversion breakpoints'
											AND(
											CASE research_name 
											WHEN "Martinez-Fundichely et al. 2013" THEN 
																				(
																						( inv_BP1s BETWEEN BP1s AND BP1e) 
																						OR 
																						( inv_BP1e BETWEEN BP1s AND BP1e )
																						OR
																						( (inv_BP1s <= BP1s)  AND (inv_BP1e >= BP1e) )
																				) 
															AND (
																						( inv_BP2s BETWEEN BP2s AND BP2e) 
																						OR 
																						( inv_BP2e BETWEEN BP2s AND BP2e )
																						OR
																						( (inv_BP2s <= BP2s) AND (inv_BP2e >= BP2e))
																						
																					)
												ELSE
																					(
																						( inv_BP1s BETWEEN RBP1s AND RBP1e) 
																						OR 
																						( inv_BP1e BETWEEN RBP1s AND RBP1e )
																						OR
																						( (inv_BP1s <= RBP1s)  AND (inv_BP1e >= RBP1e) )
																					) 
																AND (
																						(inv_BP2s BETWEEN RBP2s AND RBP2e) 
																						OR 
																						( inv_BP2e BETWEEN RBP2s AND RBP2e )
																						OR
																						( (inv_BP2s <= RBP2s) AND (inv_BP2e >= RBP2e))
																					)
												END
												);





END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP PROCEDURE IF EXISTS `update_genomic_effect` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8 */ ;
/*!50003 SET character_set_results = utf8 */ ;
/*!50003 SET collation_connection  = utf8_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = '' */ ;
DELIMITER ;;
CREATE DEFINER=`invfest_admin`@`localhost` PROCEDURE `update_genomic_effect`(IN gene_id_val INT, IN inv_id_val INT,  IN effect_val TEXT, IN funt_cons_val VARCHAR(255), IN source_val VARCHAR(255), IN user_id_val INT)
    SQL SECURITY INVOKER
BEGIN

	DECLARE previous_value_val text;
	DECLARE newer_value_val text;
	DECLARE task_val text;
	DECLARE ibp_val INT;


SELECT b.id INTO ibp_val
FROM inversions i
INNER JOIN breakpoints b ON b.id = (
    SELECT id FROM breakpoints b2
    WHERE b2.inv_id = i.id
    ORDER BY FIELD(b2.definition_method, 'manual curation', 'default informatic definition'), b2.id DESC
    LIMIT 1
) WHERE i.id = inv_id_val;

SELECT CONCAT('functional_effect: ', functional_effect,', source: ',source,', functional_consequence: ',functional_consequence) INTO previous_value_val FROM genomic_effect	WHERE gene_id = gene_id_val AND inv_id = inv_id_val AND bp_id = ibp_val;

UPDATE genomic_effect SET functional_effect = effect_val, source = source_val, functional_consequence = funt_cons_val
			WHERE gene_id = gene_id_val AND inv_id = inv_id_val AND (bp_id = ibp_val OR bp_id IS NULL);

SELECT CONCAT('functional_effect: ', functional_effect,', source: ',source,', functional_consequence: ',functional_consequence) INTO newer_value_val FROM genomic_effect	WHERE gene_id = gene_id_val AND inv_id = inv_id_val AND bp_id = ibp_val;

SET task_val = CONCAT('UPDATE genomic_effect of inv ',inv_id_val,' in gene ', gene_id_val );

CALL  save_log(user_id_val, task_val, previous_value_val, newer_value_val);


END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP PROCEDURE IF EXISTS `update_info` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8 */ ;
/*!50003 SET character_set_results = utf8 */ ;
/*!50003 SET collation_connection  = utf8_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = '' */ ;
DELIMITER ;;
CREATE DEFINER=`invfest_admin`@`localhost` PROCEDURE `update_info`(IN key_val VARCHAR(255), IN entry_val VARCHAR(255), IN info_val TEXT, IN user_id_val INT)
    SQL SECURITY INVOKER
BEGIN

DECLARE previous_value_val text;
DECLARE newer_value_val text;
DECLARE task_val text;
DECLARE current_bp_id text;


CASE entry_val  
	WHEN 'comments_pred' THEN
		SELECT comments INTO previous_value_val FROM predictions	WHERE research_id = SUBSTRING_INDEX(key_val, ';', 1) AND research_name = SUBSTRING_INDEX(key_val, ';', -1);
		UPDATE predictions SET comments = info_val WHERE research_id = SUBSTRING_INDEX(key_val, ';', 1) AND research_name = SUBSTRING_INDEX(key_val, ';', -1);
		SELECT comments INTO newer_value_val FROM predictions	WHERE research_id = SUBSTRING_INDEX(key_val, ';', 1) AND research_name = SUBSTRING_INDEX(key_val, ';', -1);
		SET task_val = CONCAT('UPDATE comments of predictions ', key_val);
		CALL  save_log(user_id_val, task_val, previous_value_val, newer_value_val);

	WHEN 'inv_bp_origin' THEN 
		SELECT origin INTO previous_value_val FROM inversions	WHERE id = key_val;
		UPDATE inversions SET origin = info_val WHERE id = key_val;
		SELECT origin INTO newer_value_val FROM inversions	WHERE id = key_val;
		SET task_val = CONCAT('UPDATE origin of inversions ', key_val);
		CALL  save_log(user_id_val, task_val, previous_value_val, newer_value_val);

	WHEN 'comments_inv' THEN 
		SELECT `comment` INTO previous_value_val FROM inversions	WHERE id = key_val;
		UPDATE inversions SET `comment` = info_val WHERE id = key_val;
		SELECT `comment` INTO newer_value_val FROM inversions	WHERE id = key_val;
		SET task_val = CONCAT('UPDATE comment of inversions ', key_val);
		CALL  save_log(user_id_val, task_val, previous_value_val, newer_value_val);

	WHEN 'comments_eh' THEN 
		SELECT `comments_eh` INTO previous_value_val FROM inversions	WHERE id = key_val;
		UPDATE inversions SET `comments_eh` = info_val WHERE id = key_val;
		SELECT `comments_eh` INTO newer_value_val FROM inversions	WHERE id = key_val;
		SET task_val = CONCAT('UPDATE comments_eh of inversions ', key_val);
		CALL  save_log(user_id_val, task_val, previous_value_val, newer_value_val);

	WHEN 'comments_bp' THEN 
		SELECT breakpoints.`comments`, 
             breakpoints.id 
        INTO previous_value_val, current_bp_id
        FROM inversions, breakpoints	
        WHERE inversions.id = key_val 
            AND (inversions.id=breakpoints.inv_id AND breakpoints.id = (
                SELECT b2.id FROM breakpoints b2
                WHERE b2.inv_id = inversions.id
                ORDER BY FIELD(b2.definition_method, 'manual curation', 'default informatic definition'), b2.id DESC
                LIMIT 1));
		UPDATE breakpoints SET `comments` = info_val WHERE id = current_bp_id;
		SELECT `comments` INTO newer_value_val FROM breakpoints	WHERE id = current_bp_id;
		SET task_val = CONCAT('UPDATE comments of breakpoint ', current_bp_id, ' from inversion ', key_val);
		CALL  save_log(user_id_val, task_val, previous_value_val, newer_value_val);

	
	

END CASE;	






END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP PROCEDURE IF EXISTS `update_inv_range` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8 */ ;
/*!50003 SET character_set_results = utf8 */ ;
/*!50003 SET collation_connection  = utf8_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = '' */ ;
DELIMITER ;;
CREATE DEFINER=`invfest_admin`@`localhost` PROCEDURE `update_inv_range`()
    SQL SECURITY INVOKER
BEGIN
	

  DECLARE inv_BP1s_val INT DEFAULT 0;
  DECLARE inv_BP1e_val INT DEFAULT 0;
  DECLARE inv_BP2s_val INT DEFAULT 0;
  DECLARE inv_BP2e_val INT DEFAULT 0;

  DECLARE inv_BP1s INT DEFAULT 0;
  DECLARE inv_BP1e INT DEFAULT 0;
  DECLARE inv_BP2s INT DEFAULT 0;
  DECLARE inv_BP2e INT DEFAULT 0;

DECLARE inversion_id_val INT;


  DECLARE no_more_rows_inversion BOOLEAN;
  DECLARE loop_cntr_inversion INT DEFAULT 0;
  DECLARE num_rows_inversion INT DEFAULT 0; 


  DECLARE inversion_cur CURSOR FOR
    SELECT id 
    FROM inversions;


	DECLARE CONTINUE HANDLER FOR NOT FOUND
    SET no_more_rows_inversion = TRUE;


OPEN inversion_cur;
	SELECT FOUND_ROWS() INTO num_rows_inversion;
WHILE loop_cntr_inversion < num_rows_inversion DO 

	FETCH  inversion_cur
	INTO	inversion_id_val;

	SELECT  b.bp1_start, b.bp1_end, b.bp2_start, b.bp2_end
	INTO inv_BP1s_val, inv_BP1e_val, inv_BP2s_val, inv_BP2e_val  
	FROM inversions i
	INNER JOIN breakpoints b ON b.id = (
			SELECT id FROM breakpoints b2
			WHERE b2.inv_id = i.id
			ORDER BY FIELD(b2.definition_method, 'manual curation', 'default informatic definition'), b2.`date` DESC
			LIMIT 1
	) WHERE i.id = inversion_id_val;

			UPDATE inversions SET range_start = inv_BP1s_val, range_end =  inv_BP2e_val, size = inv_BP2s_val -  inv_BP1e_val WHERE id = inversion_id_val;	
											
			SELECT b.bp1_start, b.bp1_end, b.bp2_start, bp2_end INTO inv_BP1s, inv_BP1e, inv_BP2s, inv_BP2e
						FROM inversions i
						INNER JOIN breakpoints b ON b.id = (
																								SELECT id FROM breakpoints b2
																										WHERE b2.inv_id = i.id
																									ORDER BY FIELD(b2.definition_method, 'manual curation', 'default informatic definition'), b2.`date` DESC
																									LIMIT 1
																								) WHERE i.id = inversion_id_val;


UPDATE predictions SET accuracy = "OUT OF BREAKPOINTS RANGE"
WHERE	inv_id = inversion_id_val
											AND (
											CASE research_name 
											WHEN "GRIAL" THEN 
														NOT (
																						( inv_BP1s BETWEEN BP1s AND BP1e) 
																						OR 
																						( inv_BP1e BETWEEN BP1s AND BP1e )
																						OR
																						( (inv_BP1s <= BP1s)  AND (inv_BP1e >= BP1e) )
																			) 
											OR NOT(
																						( inv_BP2s BETWEEN BP2s AND BP2e) 
																						OR 
																						( inv_BP2e BETWEEN BP2s AND BP2e )
																						OR
																						( (inv_BP2s <= BP2s) AND (inv_BP2e >= BP2e))
																						
																			)
											ELSE
														NOT (
																						( inv_BP1s BETWEEN RBP1s AND RBP1e) 
																						OR 
																						( inv_BP1e BETWEEN RBP1s AND RBP1e )
																						OR
																						( (inv_BP1s <= RBP1s)  AND (inv_BP1e >= RBP1e) )
																			) 
											OR NOT(
																						(inv_BP2s BETWEEN RBP2s AND RBP2e) 
																						OR 
																						( inv_BP2e BETWEEN RBP2s AND RBP2e )
																						OR
																						( (inv_BP2s <= RBP2s) AND (inv_BP2e >= RBP2e))
																						
																				)
											END
											);

UPDATE predictions SET accuracy = NULL
WHERE	inv_id = inversion_id_val AND accuracy = 'OUT OF BREAKPOINTS RANGE'
											AND (
											CASE research_name 
											WHEN "GRIAL" THEN 
																				(
																						( inv_BP1s BETWEEN BP1s AND BP1e) 
																						OR 
																						( inv_BP1e BETWEEN BP1s AND BP1e )
																						OR
																						( (inv_BP1s <= BP1s)  AND (inv_BP1e >= BP1e) )
																				) 
															AND (
																						( inv_BP2s BETWEEN BP2s AND BP2e) 
																						OR 
																						( inv_BP2e BETWEEN BP2s AND BP2e )
																						OR
																						( (inv_BP2s <= BP2s) AND (inv_BP2e >= BP2e))
																						
																					)
												ELSE
																					(
																						( inv_BP1s BETWEEN RBP1s AND RBP1e) 
																						OR 
																						( inv_BP1e BETWEEN RBP1s AND RBP1e )
																						OR
																						( (inv_BP1s <= RBP1s)  AND (inv_BP1e >= RBP1e) )
																					) 
																AND (
																						(inv_BP2s BETWEEN RBP2s AND RBP2e) 
																						OR 
																						( inv_BP2e BETWEEN RBP2s AND RBP2e )
																						OR
																						( (inv_BP2s <= RBP2s) AND (inv_BP2e >= RBP2e))
																					)
												END
												);

END WHILE ;

END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP PROCEDURE IF EXISTS `update_study` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8 */ ;
/*!50003 SET character_set_results = utf8 */ ;
/*!50003 SET collation_connection  = utf8_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_AUTO_VALUE_ON_ZERO' */ ;
DELIMITER ;;
CREATE DEFINER=`invfest_admin`@`localhost` PROCEDURE `update_study`(IN `study_name_val` varchar(255), IN `aim_val` varchar(255), IN method_val varchar(255), IN user_id_val INT)
    SQL SECURITY INVOKER
BEGIN

	IF aim_val = 'prediction' THEN


		UPDATE researchs SET aim = CONCAT_WS(',',aim,aim_val), prediction_method = IF(prediction_method = method_val, prediction_method,CONCAT_WS(',',prediction_method, method_val))
			WHERE `name` = study_name_val AND aim NOT LIKE '%prediction%';	



ELSE
		UPDATE researchs SET aim = CONCAT_WS(',',aim,aim_val), validation_method = IF(validation_method = method_val, validation_method,CONCAT_WS(',',validation_method, method_val))
			WHERE `name` = study_name_val AND aim NOT LIKE '%validation%';


	END IF;

END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP PROCEDURE IF EXISTS `update_validation` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8 */ ;
/*!50003 SET character_set_results = utf8 */ ;
/*!50003 SET collation_connection  = utf8_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = '' */ ;
DELIMITER ;;
CREATE DEFINER=`invfest_admin`@`localhost` PROCEDURE `update_validation`(IN `type` VARCHAR(255),IN `inv_name_val` VARCHAR(255), IN `research_name_val` VARCHAR(255),IN `val_id` INT, IN `validation_val` VARCHAR(255),  IN `validation_method_val` VARCHAR(255),IN `PCRconditions_val` VARCHAR(255), IN `primer_val` VARCHAR(255),IN `validation_comment_val` TEXT ,IN `checked_val` VARCHAR(255), IN user_id_val INT)
    SQL SECURITY INVOKER
BEGIN

 
  DECLARE inv_id_val INT;
  DECLARE pred_id_val INT;
  DECLARE pred_research_name_val VARCHAR(255);

  DECLARE inversion_status_val  VARCHAR(255);
  DECLARE predition_status_val  VARCHAR(255);
  DECLARE current_inv_status_val  VARCHAR(255);
#  DECLARE val_id  VARCHAR(255);

  DECLARE check_current_status VARCHAR(255);

  DECLARE no_more_rows_inversion BOOLEAN;
  DECLARE loop_cntr_inversion INT DEFAULT 0;
  DECLARE num_rows_inversion INT DEFAULT 0;

  DECLARE previous_value_val text;
  DECLARE newer_value_val text;
  DECLARE task_val text; 

# Check if there is an objective

IF type IN ('ADD', 'UPDATE') THEN
 	
    # We want to add the validation
	IF type IN ('ADD') THEN
		
        # Select the inversion ID
		SELECT id INTO inv_id_val
			FROM inversions
			WHERE  name =  inv_name_val;
            
		# Add validation to the database, and save log
		INSERT INTO validation	(research_name, inv_id, method, status, experimental_conditions, primers, comment, checked)
			VALUES (research_name_val, inv_id_val, validation_method_val, validation_val, PCRconditions_val, primer_val, validation_comment_val, checked_val);	

		SET val_id = LAST_INSERT_ID() ;

		SET task_val = CONCAT('INSERT new validation to inv ',inv_id_val);
		CALL  save_log(user_id_val, task_val, "none", val_id);

		# Update the validation amount in the inversion
		SELECT validation_amount INTO previous_value_val 
			FROM inversions 
			WHERE id = inv_id_val;
		UPDATE inversions 
			SET validation_amount = validation_amount+1 
			WHERE id = inv_id_val;
		SELECT validation_amount INTO newer_value_val 
			FROM inversions 
			WHERE id = inv_id_val;
		SET task_val = CONCAT('UPDATE validation_amount of inv ',inv_id_val);
		CALL  save_log(user_id_val, task_val, previous_value_val, newer_value_val);

	# We want to update an existing validaiton
    ELSE 
    
		# Select the inversion ID
		SELECT inv_id INTO inv_id_val 
			FROM validation
			WHERE  id =  val_id;
		
        # Update the validation
		UPDATE validation SET status = validation_val,experimental_conditions=  PCRconditions_val ,primers = primer_val,comment =validation_comment_val ,  checked = checked_val WHERE id=val_id;
		SET task_val = CONCAT('EDIT validation with id ',val_id, ' from inv ', inv_id_val );
		CALL  save_log(user_id_val, task_val, val_id, val_id); 

	END IF;

# STEP 3: search a forced status
        SELECT checked INTO check_current_status
            FROM validation
            WHERE inv_id = inv_id_val
            ORDER BY checked DESC
            LIMIT 1 ;

	# If the validation is not a Breakpoint curation
	IF validation_val != 'BP curation' THEN
									
		SET inversion_status_val = validation_val;
	
		# STEP 1: if our validation is 'checked', delete previous checks, force status and save log
		IF checked_val = 'yes' THEN
			# All the other validations will not be checked
			UPDATE validation SET checked = 'not' WHERE inv_id =  inv_id_val AND id != val_id;
			
            # Now we edit the inversion	
			SELECT status INTO previous_value_val FROM inversions WHERE id = inv_id_val;
			UPDATE inversions SET status = inversion_status_val WHERE id = inv_id_val;
			SELECT status INTO newer_value_val FROM inversions WHERE id = inv_id_val;
			SET task_val = CONCAT('UPDATE status of inv ',inv_id_val);
			CALL  save_log(user_id_val, task_val, previous_value_val, newer_value_val);
	   
		# STEP 2: if our validation is not 'checked':
		ELSE

			# STEP 3: search a forced status
			SELECT checked INTO check_current_status
				FROM validation
				WHERE inv_id = inv_id_val
				ORDER BY checked DESC
				LIMIT 1 ;

			# STEP 4: if there is not a forced (checked) status, compare and merge the information
			IF check_current_status != 'yes' THEN
					 
				# STEP 5: compare results in validations
					
					 # Two specific, contradictory statuses
						IF (SELECT EXISTS(SELECT status FROM validation WHERE inv_id = inv_id_val AND status = 'FALSE')) = 1 AND 
						   (SELECT EXISTS(SELECT status FROM validation WHERE inv_id = inv_id_val AND status = 'TRUE')) = 1 THEN
										
									  SELECT status INTO previous_value_val FROM inversions WHERE id = inv_id_val;
									  UPDATE inversions SET status = 'Ambiguous' WHERE id = inv_id_val;
									  SELECT status INTO newer_value_val FROM inversions WHERE id = inv_id_val;
									  SET task_val = CONCAT('UPDATE status of inv ',inv_id_val);
									  CALL  save_log(user_id_val, task_val, previous_value_val, newer_value_val);
					 # One specific status:TRUE
						ELSEIF  (SELECT EXISTS(SELECT status FROM validation WHERE inv_id = inv_id_val AND status = 'TRUE')) = 1 THEN
									  SELECT status INTO previous_value_val FROM inversions WHERE id = inv_id_val;
									  UPDATE inversions SET status = 'TRUE' WHERE id = inv_id_val;
									  SELECT status INTO newer_value_val FROM inversions WHERE id = inv_id_val;
									  SET task_val = CONCAT('UPDATE status of inv ',inv_id_val);
									  CALL  save_log(user_id_val, task_val, previous_value_val, newer_value_val);
					# One specific status: FALSE

						ELSEIF (SELECT EXISTS(SELECT status FROM validation WHERE inv_id = inv_id_val AND status = 'FALSE')) = 1 THEN
									  SELECT status INTO previous_value_val FROM inversions WHERE id = inv_id_val;
									  UPDATE inversions SET status = 'FALSE' WHERE id = inv_id_val;
									  SELECT status INTO newer_value_val FROM inversions WHERE id = inv_id_val;
									  SET task_val = CONCAT('UPDATE status of inv ',inv_id_val);
									  CALL  save_log(user_id_val, task_val, previous_value_val, newer_value_val);
					# All non specific results
						ELSE 
										
									  SELECT status INTO previous_value_val FROM inversions WHERE id = inv_id_val;
									  UPDATE inversions SET status = 'ND' WHERE id = inv_id_val;
									  SELECT status INTO newer_value_val FROM inversions WHERE id = inv_id_val;
									  SET task_val = CONCAT('UPDATE status of inv ',inv_id_val);
									  CALL  save_log(user_id_val, task_val, previous_value_val, newer_value_val);
						END IF;	
				
            # STEP 6: if there is  a forced (checked) status, force status
			ELSEIF check_current_status = 'yes' THEN
				SELECT status INTO inversion_status_val FROM validation WHERE inv_id = inv_id_val ORDER BY checked DESC LIMIT 1 ;
				SELECT status INTO previous_value_val FROM inversions WHERE id = inv_id_val;
				UPDATE inversions SET status = inversion_status_val WHERE id = inv_id_val;
				SELECT status INTO newer_value_val FROM inversions WHERE id = inv_id_val;
				SET task_val = CONCAT('UPDATE status of inv ',inv_id_val);
				CALL  save_log(user_id_val, task_val, previous_value_val, newer_value_val);
		   				
			END IF; # Exiting check
		END IF; # Out validation is checked

		# This process has been generalized to avoid using cursors. 

		SET predition_status_val = CONCAT('on_', inversion_status_val);
		SELECT status INTO previous_value_val FROM predictions WHERE inv_id = inv_id_val LIMIT 1;
		UPDATE predictions SET status =  predition_status_val WHERE inv_id = inv_id_val;
		SET task_val = CONCAT('UPDATE status of predictions from inversion ', inv_id_val);
		CALL  save_log(user_id_val, task_val, previous_value_val, predition_status_val);		
							
	END IF; # The inversio nis not a breakpoint curation

ELSE

	SELECT CONCAT("Incorrect type, please specify 'ADD',  or 'UPDATE'");

END IF; # There is a good type



END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2019-06-07 17:10:36
