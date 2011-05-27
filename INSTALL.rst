INSTALL FROM THE APP EXAMPLE
============================
::

  mkdir project
  cd project
  
  # Confeature dir
  git clone git://github.com/Godefroy/confeature.git confeature
  
  # App dir
  wget --no-check-certificate https://github.com/Godefroy/confeature-example/tarball/master -O app.tar.gz
  tar -xf app.tar.gz
  rm app.tar.gz
  mv *-confeature-* app
  
  # Data dir
  mkdir -p data/logs
  mkdir -p data/tmp


Quick deployment
----------------

``project/app/cli/export.sh``
