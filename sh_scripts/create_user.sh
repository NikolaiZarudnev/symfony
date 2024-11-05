#!/bin/bash
cd -P /home/nikolay/PhpstormProjects/symfony || exit
RAND_NUM=$RANDOM
php bin/console app:create-user test-$RAND_NUM@test.test test-$RAND_NUM@test.test ROLE_USER true