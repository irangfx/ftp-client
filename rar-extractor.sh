oldPassword='tarhan.ir';
newPassword='irangfx.com';

extract () (
  set -e
  archive=$1
  case "$archive" in
    -) :;; # read from stdin
    /*) :;; # already an absolute path
    *) archive=$PWD/$archive;; # make absolute path
  esac
  target=$2
  program=$3
  if [ -e "$target" ]; then
    echo >&2 "Target $target already exists, aborting."
    return 3
  fi
  case "$target" in
    /*) parent=${target%/*};;
    */[!/]*) parent=$PWD/${target%/*};;
    *) parent=$PWD;;
  esac
  temp=$(TMPDIR="$parent" mktemp -d)
  (cd "$temp" && $program "$archive")
  root=
  for member in "$temp/"* "$temp/".*; do
    case "$member" in */.|*/..) continue;; esac
    if [ -n "$root" ] || ! [ -d "$member" ]; then
      root=$temp # There are multiple files or there is a non-directory
      break
    fi
    root="$member"
  done
  if [ -z "$root" ]; then
    # Empty archive
    root=$temp
  fi
  mv -v -- "$root" "$target"
  if [ "$root" != "$temp" ]; then
    rmdir "$temp"
  fi
)

process () {
  dir=${1%.*}
  case "$1" in
    *.rar|*.RAR) program="unrar x -p$oldPassword";;
    *.zip|*.ZIP) program="unzip -P $oldPassword";;
    *) echo >&2 "$0: $1: unsupported archive type"; exit 4;;
  esac
  if [ -d "$dir" ]; then
    echo >&2 "$0: $dir: directory already exists"
    exit 1
  fi
  extract "$1" "$dir" "$program"
}

newDir=${2%.*}

mv $1 $2
process "$2";
rm $2
cd $newDir
sed 's+www.tarhan.ir/academy+irangfx.com+g' Tarhan.ir.url > IranGFX.com.url
rm Tarhan.ir.url
cd ..
rar -p$newPassword a -r -rr5 $2 ./$newDir
rm -rf $newDir