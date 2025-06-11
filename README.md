echo "# mert-test1" >> README.md
git init
git add README.md
git commit -m "first commit"
git branch -M main
git remote add origin https://github.com/mertsadekist/mert-test1.git
git push -u origin main
