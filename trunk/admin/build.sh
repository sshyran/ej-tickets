#!/bin/bash

for i in db-lead_categories.php db-roles.php db-site_users.php db-user_roles.php db-users.php db-lead_fields.php db-leads.php
do
    rm -v $i 2> /dev/null
    cp -v db-all.php $i
done

sleep 5;
