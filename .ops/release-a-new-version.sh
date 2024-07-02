#!/bin/bash
set -e # tells the shell to exit if a command returns a non-zero exit status

# usage:    sh .ops/release a new version.sh

echo "What is your new version (A.B.C)?"
read NEW_VERSION
echo "will release the version $NEW_VERSION ? (y/n)";
read CONFIRMATION
if [ "$CONFIRMATION" == "y" ]; then
  echo "Release a new version $NEW_VERSION"
  git tag -a v${NEW_VERSION} -m "Release version ${NEW_VERSION}"
  git push origin v${NEW_VERSION}
else
  echo "[CANCEL] release a new version $NEW_VERSION"
fi
