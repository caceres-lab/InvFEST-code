#------------------------------------------------------------------------------------
# Copyright (C) 2014, by the authors,  All rights reserved.
#
#    This program is free software: you can redistribute it and/or modify
#    it under the terms of the GNU General Public License as published by
#    the Free Software Foundation, either version 3 of the License, or
#    (at your option) any later version.
#
#    This program is distributed in the hope that it will be useful,
#    but WITHOUT ANY WARRANTY; without even the implied warranty of
#    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
#    GNU General Public License for more details.
#
#    You should have received a copy of the GNU General Public License
#    along with this program.  If not, see <http://www.gnu.org/licenses/>.
#	
# Name:      	README.txt 
#
# Author(s): 	Alexander Martínez-Fundichely & Sònia Casillas
#		Comparative and Functional Genomics group
#		Institut de Biotecnologia i de Biomedicina
#		Universitat Autònoma de Barcelona, Spain
#
# Contact:	batscherow@gmail.com, sonia.casillas@uab.cat
#
#     Status.......: 3.1 (Released)
#     Status Date..: 01.04.2014
#     Purpose......: some basic information about the GRIAL distribution
#
# Changes   Date(DD.MM.YYYY)   NAME   DESCRIPTION
#
#------------------------------------------------------------------------------------

Geometric Rules Inversion ALgorithm (GRIAL)

Readme

SUMMARY ==============================================

GRIAL is a software for predicting inversions and refining their 
breakpoints based on the computation of some geometric rules specific 
to inversions in a PEM assay. GRIAL can also score all the predicted 
inversions based on the number of concordant PEMs in the region of the 
inversion, and the expected number of discordant PEMs supporting each 
breakpoint given the inversion size, the repetitive nature of the sequence 
of the breakpoints, and the sequencing coverage.

If you use GRIAL in your research, please cite: Martínez-Fundichely 
et al. (in preparation)


CONTENTS =============================================

This section lists the files distributed with GRIAL Release 1.0, 
GRIAL_RELEASE_1.0.tar.gz.

(i) Documentation (under doc/ subdirectory):

* Quick start Guide: GRIAL_Quickstart_Guide.txt
	A quick introduction to GRIAL by using a sample data set 
	(available for download from the GRIAL website. 
	Please download and unzip to testing/input/).

* GRIAL Manual: MANUAL.txt
	A complete manual of use to GRIAL.

* GRIAL Release Notes: RELEASE_NOTES.txt
	Documentation of new features developed with each GRIAL Release.

* GRIAL FAQ: FAQ.txt
	A list of "Frequently Asked Questions" regarding GRIAL.

* GPL license information: COPYING.txt
	A copy of the GPL license.


(ii) GRIAL Software

* Main Perl script of GRIAL: GRIAL.pl

* Perl script for scoring inversions: GRIALscore.pl

* Accessory Perl script for generating repeats/segdups tracks: 
  generateRepeatTracks.pl


(iii) GRIAL config file

* Configuration file: GRIAL.config


(iv) Sample Files (under testing/input/ subdirectory), 
     available in a separate download:

* Discordant data: discordantFosmids.txt 

* Concordant data: concordantFosmids.chr* (one file per chromosome)


(v) Third-party scripts

* fetchChromSizes from UCSC (http://hgdownload.cse.ucsc.edu/admin/exe/linux.x86_64/): 
  fetchChromSizes


