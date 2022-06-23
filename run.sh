#!/bin/bash

date=$1

./bin/console worklog:upload --from=$date
