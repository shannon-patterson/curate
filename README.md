# curate
A set of scripts I use to help me curate my music collection

Right now it's not much but these scripts help me manage
my music collection.  I have a large directory where I dump
all the music I need to curate (decide to keep or throw 
away).  This foler is too annoying to deal with.  I 
prefer to queue up about ten files and deal with them 
exclusively.  In addition to this, I want to get through 
the entirety of an artist's work before moving on to the 
next artist, and I want to approve of each song three times
before it is moved into its final destination. 

These scripts currently help me pull files into a curation
directory, and queue these files into VLC.  In the curation 
directory, there are subdirectories for each artist, with a
directory under it which identifies how many passes I have 
made through the artist's songs.  When all the files for an
artist have been exhausted, a message is output that the 
artist has either levelled up, or is complete.

If an artist has levelled up, the scripts move all of the 
songs in that directory back into the large source folder, 
and incremented the subdirectory by one.  If it is complete,
the files are left where they are, for me to confirm that 
all files are tagged appropriately and there are no 
duplicates.


**Long-Term Goal**

The long-term goal of this project is to use this pipeline
I use to learn angular through building a web-based music 
player and playlist.  Instead of manually moving files into
the artist subfolders when I approve of a file, I want 
to be able to click an 'approve' checkbox in my music 
player which will trigger that action.  It would also be
nice to automate some of the duplicate checking and tagging
but that is a much longer term goal.  

### TODO

* use autoloader rather than requires
* move all paths and constants into a config class
* move PHP classes into proper folder structure 
  according to the namespaces I have created
* start on webapp part of project