SimpleDB
========

Folder Schema:

<pre>
PROJECT ROOT
    models
        Simple_DB.php
    json
        FOLDERS HERE
            FILES HERE
</pre>
    
If you create the folder structure above and autoload or include this class, everything will work out of the box. 
If not, you will need to edit the constructor to tell the class where you want to store your files. The default 
location is a json folder in the project root. In other words, the storage is located at the same folder level as 
the folder which contains your classes.

If you are interested in protecting your data from prying eyes, use a .htaccess file in the json folder:

<pre>
Order Deny, Allow
Deny From All
</pre>

Usage:

Instantiate the class with the name of the type of object you are storing. If you are storing cars, the code would look 
like this:

<pre>
$simpleDB = new Simple_DB('car');
</pre>

The class contains four basic methods which attempt to mimic HTTP methods:

<pre>
Simple_DB::get($id);
Simple_DB::post($content);
Simple_DB::put($id, $content);
Simple_DB::delete($id);
</pre>

If you have existing cars that you want to retrieve, you can do so like this:

<pre>
$simpleDB->get();
</pre>

Calling <code>get</code> without the ID parameter will return an array of objects containing all of that type of object. 
The code above will give you every car in storage.
