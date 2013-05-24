#!/usr/bin/env bash
rsync --delete -avze \
--filter="-rsp_/.git/" \
--filter="-rsp_/.gitignore" \
--filter="-rsp_/drushrc.php" \
--filter="-rsp_/files/" \
--filter="-rsp_/modules/development/" \
--filter="-rsp_/private/" \
--filter="-rsp_/README.md" \
--filter="-rsp_/settings.php" \
$1 $2;

## Manually delete .git folder since it isn't excluding.
rm -rf $2.git/