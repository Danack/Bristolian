
# These placeholders should only be run once, and only on a dev box.

php cli.php admin:create_user testing@example.com testing
php cli.php admin:create_user danack@example.com testing
php cli.php room:create  "Housing" "A place to discuss the problem that is BCC housing"
php cli.php room:create  "Misc" "A place to discuss everything else"

php cli.php debug:add_meme test/fixtures/memes/came-to-laugh-not-feel.jpg "sad,wojak,feel"  "I came here to laugh not to feel"
php cli.php debug:add_meme test/fixtures/memes/one_yikes.jpeg "yikes,reaction" "You have been awarded on yike from the national committee of yikes"
php cli.php debug:add_meme test/fixtures/memes/sipping_tea_yikes.jpeg "USA,tea" "alex morgan celebrates scoring against england by pretending to sip tea, I'm English and I laughed she pretended to sip some tea it was funny people need to chill out. It's what we're known for. It's like if the English pretended  to eat a big mac or shoot up a school."
php cli.php debug:add_meme test/fixtures/memes/sword_not_safe.png "pratchett,sword" "You can't give her that! It's not safe! It's a sword. They're not meant to be safe. She's a child. It's educational. What if she cuts her self. That will be an important lesson"
php cli.php debug:add_meme test/fixtures/memes/wednesday_dudes.jpeg "wednesday,dudes,abyss,void" "You are not along in this abyssal darkness. I am here, and we shall face the wednesday as Dudes".