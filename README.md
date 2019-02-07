# File-Utilities

A collection of some File/Folder Utilities that might be useful :)

## Features

- Flatten Directory Tree
- Randomize Directory

## Installation

Requirements: 
- [Docker](https://www.docker.com)
- (optional for mac) [docker.sync](http://docker-sync.io)

Note: This docker buildup is a default docker-configuration from [thhan/Docker-Builder](https://github.com/thhan/Docker-Builder)

1. Clone the repository `git clone git@github.com:bartrail/file-utilities.git`
2. Edit `docker/docker-compose.yml.dist` and replace line `- /Users/con/Pictures:/var/www/Pictures` with a directory you want to work in.
   
   The left side of the colon `:` is your local path
   
   The right side of the colon `:` is the path inside the virtual machine you will be using later.
   
   You can add as many path-mappings as you wish, as long as it is an entry beneath `php / volumes`
3. Go to `cd docker` and run `sh docker/docker.sh -l` to start the virtual machine.

   `-l` will directly log you into the virtual machine. Alternatively you can open a new terminal window and run `sh docker-ssh.sh` from inside the `docker` directory to log in afterwards.

### Flatten Directory Tree

Imagine a directory structure with many files and subdirectories in it (like a huge photo gallery) and you like view them with a nice slideshow tool - but this slideshow tool is unable to walk through sub-directories. This tool might be useful.

### Usage

Show Help `sf app:flatten-directory-tree --help`
```bash
Usage:
  app:flatten-directory-tree [options] [--] <source-dir> <target-dir>

Arguments:
  source-dir
  target-dir
  
Options:
      --overwrite        Overwrite existing files (asks for every file unless -n is given)
  -i, --ignore[=IGNORE]  File patterns to be ignored [default: [".DS_Store"]] (multiple values allowed)
```

Run
```
sf app:flatten-directory-tree /var/www/Pictures/vacations /var/www/Pictures/vacations_flat
```

#### Options

- `--overwrite` Overwrite existing files. Otherwise it will ask for every file
- `--ignore` Ignore filetypes with a simple preg_match

    ```
    sf app:flatten-directory-tree /var/www/Pictures/vacations /var/www/Pictures/vacations_flat --ignore=".thumb" --ignore=".DS_Store" --ignore=".mp4"
    ```

- `-n` Don't ask for overwriting
  - when `--overwrite` is given, this will overwrite any file found.
  - when `--overwrite` is *not* given, this will skip the user prompt and therefore will not overwrite files
  

### Randomize Directory

Randomizes all files in a directory by adding a randomized prefix at the beginning of every filename.

### Usage

...