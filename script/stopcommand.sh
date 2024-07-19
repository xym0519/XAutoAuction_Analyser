#!/bin/bash
ps ax | grep '{command}' | grep -v grep | awk '{print $1}' | xargs kill -9
