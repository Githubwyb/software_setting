#!/bin/bash

open_dir="$1"
if [ -z "$open_dir" ]; then
    open_dir="."
fi

cd "$open_dir"

real_dir="$(pwd -P | sed 's/\///' | sed 's/\//:\\/' | sed 's/\//\\/g')"
explorer "$real_dir"
cd - &>/dev/null

