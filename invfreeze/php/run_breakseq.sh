#!/bin/sh

#******************************************************************************
#	RUN_BREAKSEQ.PHP
#
#	Runs BreakSeq and add_BS_results.php in two followed steps:
#	1) Runs BreakSeq (BreakSeq annotation). 
#	   It runs BreakSeq using as input the file stored at /home/invfest/BPSeq/breakseq_annotated_gff/input.gff, which is always generated automatically as output file when new inversions are added to the database by any of the following mechanisms:
#		1. php/add_prediction.php
#		2. php/add_merge_inversions.php
#		3. php/add_split_inversions.php
#		4. php/add_validation.php
#	The resulting output file after running BreakSeq is always stored at /home/invfest/bioinformatics/BPSeq/breakseq_annotated_gff/Results/input.gff, which already contains the inversions with the BreakSeq annotation.
#	2) Runs add_BS_results.php (Update the BreakSeq annotation to the db).
#	   It adds to the database the BreakSeq annotation from the inversions of the output file generated in the above step by BreakSeq
#*******************************************************************************

# Activate local Python
source /home/invfest/python/venv_invfest/bin/activate

# Execute BreakSeq and add_BS_results.php
nohup /home/invfest/bioinformatics/breakseq-1.3/breakseq annotate /home/invfest/BPSeq/breakseq_annotated_gff/input.gff /home/invfest/BPSeq/breakseq_annotated_gff/Results 100 && nohup php /var/www/html/invdb/php/add_BS_results.php &
