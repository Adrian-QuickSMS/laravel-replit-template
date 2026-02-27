{ pkgs }: {
  deps = [
    pkgs.php83
    pkgs.php83Packages.composer
    pkgs.php83Extensions.pgsql
    pkgs.php83Extensions.pdo_pgsql
    pkgs.php83Extensions.mbstring
    pkgs.php83Extensions.xml
    pkgs.php83Extensions.curl
    pkgs.php83Extensions.gd
    pkgs.php83Extensions.zip
    pkgs.nodejs_20
    pkgs.unzip
    pkgs.git
  ];
}
