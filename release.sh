#!/bin/bash
# Usage: ./release.sh 1.0.1
set -e

VERSION=$1
if [ -z "$VERSION" ]; then
  echo "Usage: ./release.sh <version>  e.g.  ./release.sh 1.0.1"
  exit 1
fi

MAIN_FILE="cftgroup-forms.php"

# Bump version in plugin header and constant
sed -i '' "s/Version:      .*/Version:      $VERSION/" "$MAIN_FILE"
sed -i '' "s/define( 'CFTG_VERSION',     '.*' )/define( 'CFTG_VERSION',     '$VERSION' )/" "$MAIN_FILE"

echo "✓ Bumped version to $VERSION"

git add "$MAIN_FILE"
git commit -m "Release v$VERSION"
git tag "v$VERSION"
git push && git push --tags

echo ""
echo "✓ Pushed tag v$VERSION — GitHub Actions is building the zip now."
echo "  WordPress will show 'Update available' on the next check."
