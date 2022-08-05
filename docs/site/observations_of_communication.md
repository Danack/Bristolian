
# Observations of communication







## Context always matters

One thing that unpleasant people do is try to 'win' an argument by demanding either:

i) you use their definition of a word, or
ii) you provide your own definition of a word. 

This is a trap.

Although the meaning of a word can defined precisely for use in a given context, outside of that context, the meaning could be completely different.

This should not be too surprising to anyone is able to read English aloud:

> We must polish the Polish furniture.
> He could lead if he would get the lead out.
> The farm was used to produce produce.
> The dump was so full that it had to refuse more refuse.
> The soldier decided to desert in the desert.
> This was a good time to present the present.
> A bass was painted on the head of the bass drum.
> When shot at, the dove dove into the bushes.
> I did not object to the object.
> The insurance was invalid for the invalid.
> The bandage was wound around the wound.


The last one is particularly nice, in my opinion.





Apropos of nothing, I wrote shortly after reading  ["JK Rowling is a bigot"](https://burningbird.net/jk-rowling-is-a-bigot/), which offers a useful suggestion of the terms of "assigned female at birth" "assigned male at birth".

They’re inclusive terms that can be used in debates such as those around the right to control our access to abortion, to pregnancy prevention aids, and to healthcare used to treat gender dysphoria.




## Trolling is always possible 


https://www.youtube.com/watch?v=fgxElzuyyr8


## Forms of communication can be unknowable



https://www.youtube.com/watch?v=l4bmZ1gRqCc


// repeatable in testing, crypto in production
$rng = $is_production
? new \Random\Engine\Secure()
: new \Random\Engine\PcgOneseq128XslRr64(1234);

// Separate engines don't interfere with each other 
$engine_1 = new \Random\Engine\Xoshiro256StarStar(1234);
$engine_2 = new \Random\Engine\Xoshiro256StarStar(4321);

foo($engine_1);
bar($engine_2);

