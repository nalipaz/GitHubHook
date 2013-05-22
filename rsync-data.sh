#!/usr/bin/env bash
rsync -avz --exclude-from $1 $2 $3