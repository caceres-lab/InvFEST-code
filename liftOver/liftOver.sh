cd /var/www/invdb/liftOver/

liftOver -positions liftOver.in hg19ToHg18.over.chain.gz liftOver.out liftOver.unmapped

rm *bed*
