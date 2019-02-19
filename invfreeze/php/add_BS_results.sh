#!/bin/sh
nohup /home/invfest/bioinformatics/breakseq-1.3/breakseq annotate /home/invfest/BPSeq/breakseq_annotated_gff/input.gff /home/invfest/BPSeq/breakseq_annotated_gff/Results 100 && nohup php ./add_BS_results.php &
