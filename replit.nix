{ pkgs }: {
  deps = [
    pkgs.php83
    pkgs.php83Packages.composer
    pkgs.nodejs_20
    pkgs.unzip
  ];
}