#!/bin/sh
nohup /home/shareddata/Bioinformatics/BPSeq/software/breakseq-1.3/breakseq annotate /home/shareddata/Bioinformatics/BPSeq/breakseq_annotated_gff/input.gff /home/shareddata/Bioinformatics/BPSeq/breakseq_annotated_gff/Results 100 && nohup php /var/www/mcaceres-lab/invdb-dev/php/add_BS_results.php &
