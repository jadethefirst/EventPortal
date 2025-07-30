#!/bin/bash

# Navigate to the folder this script is in
cd "$(dirname "$0")"

# Ask for commit message
echo "Enter a commit message:"
read msg

# Add, commit, and push
git add .
git commit -m "$msg"
git push

echo "✅ All changes pushed to GitHub!"
