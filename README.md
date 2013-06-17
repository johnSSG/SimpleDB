SimpleDB
========
SimpleDB is PHP class which creates a simple API for dealing with JSON document storage.

<h3>Folder Schema</h3>

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

<h3>Basic Usage</h3>

Instantiate the class with the name of the type of object you are storing. If you are storing cars, the code would look 
like this:

<pre>
$simpleDB = new Simple_DB('car');
</pre>

This will create a folder for cars. You can think of that folder as a "table."

The class contains four basic methods which attempt to mimic HTTP methods:

<pre>
Simple_DB::get($id);
Simple_DB::post($content);
Simple_DB::put($id, $content);
Simple_DB::delete($id);
</pre>

GET
If you have existing cars that you want to retrieve, you can do so like this:

<pre>
$simpleDB->get();
</pre>

Calling <code>get</code> without the ID parameter will return an array of objects containing all of that type of object. 
The code above will give you every car in storage. Calling <code>get<code> with an ID parameter returns a single object.

POST

<pre>
$simpleDB->post($content);
</pre>

Post creates a new "row" or JSON document in storage. It creates an ID for the document and posts your object in plain 
text to that file. The file will be <code>YOURID.json</code>. You can pass post a string, array or object; but it your 
data will get converted to a JSON object in the end.
