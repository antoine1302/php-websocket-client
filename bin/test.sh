#!/usr/bin/env bash

PROJECT_DIR="$( cd "$(dirname "$0")/.." && pwd )"

cd "${PROJECT_DIR}" || exit

case "$1" in
    static)
        make phpcs
        ;;
    static-analyze)
        make phpstan
        ;;
    cs-fix)
        make phpcbf
        ;;
    unit)
        make tests
        ;;
    testdox)
        make testdox
        ;;
    *)
        echo "Use $0 {static|cs-fix|unit|testdox|static-analyze} in order to run static or unit tests"
        exit 1;
        ;;
esac
