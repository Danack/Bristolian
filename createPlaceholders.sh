
set -e
set -x

# These placeholders should only be run once, and only on a dev box.

php cli.php admin:create_user testing@example.com testing
php cli.php admin:create_user danack@example.com testing
php cli.php room:create  "Housing" "A place to discuss the problem that is BCC housing"
php cli.php room:create  "Off-topic" "A place to discuss everything else"

php cli.php room:create  "FOI advice" "A place to discuss FOI requests"

php cli.php room:add_file  "Housing" test/fixtures/pdfs/sample.pdf
php cli.php room:add_file  "Housing" test/fixtures/pdfs/example_different_layout.pdf

php cli.php room:add_link  "Housing" "https://google.com/" "Link title" "Link description"
php cli.php room:add_video  "Housing" "https://www.youtube.com/watch?v=q84psZX6MbA" "Video title" "Video description"
php cli.php room:add_video_clip  "Housing" "https://www.youtube.com/watch?v=q84psZX6MbA" "1:15" "4:15" "Clip title" "Clip description"
php cli.php room:add_file_annotation "Housing" "sample.pdf" '{"title":"Nulla consequat quam ut nisl - annotation.","highlights_json":"[{\"page\":0,\"left\":101,\"top\":392,\"right\":264,\"bottom\":407}]","text":""}'

php cli.php room:add_tag "Housing" "tag from cli"
php cli.php room:add_tag "Housing" "tag from cli 2" "tag description"

php cli.php room:add_annotation_tag "Housing" "Nulla consequat quam ut nisl - annotation." "tag from cli"


php cli.php debug:add_meme test/fixtures/memes/came-to-laugh-not-feel.jpg "sad,wojak,feel"  "I came here to laugh not to feel"
php cli.php debug:add_meme test/fixtures/memes/one_yikes.jpeg "yikes,reaction" "You have been awarded on yike from the national committee of yikes"
php cli.php debug:add_meme test/fixtures/memes/sipping_tea_yikes.jpeg "USA,tea" "alex morgan celebrates scoring against england by pretending to sip tea, I'm English and I laughed she pretended to sip some tea it was funny people need to chill out. It's what we're known for. It's like if the English pretended  to eat a big mac or shoot up a school."
php cli.php debug:add_meme test/fixtures/memes/sword_not_safe.png "pratchett,sword" "You can't give her that! It's not safe! It's a sword. They're not meant to be safe. She's a child. It's educational. What if she cuts her self. That will be an important lesson"
php cli.php debug:add_meme test/fixtures/memes/wednesday_dudes.jpeg "wednesday,dudes,abyss,void" "You are not along in this abyssal darkness. I am here, and we shall face the wednesday as Dudes".
