

// Import functions and structures from other files
import {describe, expect, test, it} from '@jest/globals';
import {split_tweets, Numbering} from "./TwitterSplitterPanel";


// Define an exact length of tweet
let exactly_280_characters = "1234567 ".repeat(35);

// Information on the length of tweets is available here:
// https://developer.twitter.com/en/docs/counting-characters
//
// "The current length of a URL in a Tweet is 23 characters, even
// if the length of the URL would normally be shorter."

// TypeScript
interface TweetTests{
    input: string;
    expected: string[];
}
let multi_tweet = `Because I think it's the wrong way to think about them.

Programmers should be thinking about the abstraction first, trying to find the simplest possible 'contract' that solves the problem they are currently trying to solve. It's fine and appropriate for programmers to create a unique abstraction for each problem you face when writing a program. Some of those abstractions might be useful in subsequent projects, but you would only find that out when working on the next project.

The words in that book, and the mindset of many C++ programmers in the 80's/90's, was to try to identify bits of code that look similar, and then produce a common set of abstractions that can be used across many projects.

This led to a lot of wasted effort in trying to make the subtly wrong abstraction fit.

tl:dr code re-use is a lie.

I think Sandi Metz's talk + blog post on "duplication is cheaper than the wrong abstraction" also explains how 'wrong' thinking about abstraction can have a high cost: https://sandimetz.com/blog/2016/1/20/the-wrong-abstraction`;


// This tweet should be split into three.
let multi_tweet_three = `It never seems either entirely appropriate or wise for a politician to comment on the sentences received by individuals. The reasons for the sentencing are here, if you're bold enough to comment on which part you think is wrong: https://twitter.com/BarristerSecret/status/1835673864614887851

But if you are going to be speaking on how awful our legal system is, perhaps you should say something about the massive backlog in trials. Apparently serious crimes are taking multiple years to come to trial, and the backlog is getting worse: https://x.com/BarristerSecret/status/1833781105490534706

I have a strong suspicion that the court backlog is going to block the governments ability to pass the Renters Rights Bill, as the property owners are going to raise a massive stink, and it's not clear that the courts would even be able to cope. https://centralhousinggroup.com/worse-court-delays-after-labour-renters-rights-bill/`;


let cases: TweetTests[] = [
    {
        input: "This is just over 280 characters. This is just over 280 characters. This is just over 280 characters. This is just over 280 characters. This is just over 280 characters. This is just over 280 characters. This is just over 280 characters. This is just over 280 characters. This is just over 280 characters.",
        expected: [
            "This is just over 280 characters. This is just over 280 characters. This is just over 280 characters. This is just over 280 characters. This is just over 280 characters. This is just over 280 characters. This is just over 280 characters. This is just over 280 characters.",
            "This is just over 280 characters." // The space at the start is trimmed.
        ],
    },
    {
        input: exactly_280_characters +
            "\n" + "the newline should be cropped",
        expected: [
            exactly_280_characters,
            "the newline should be cropped",
        ],
    },

  {
    input: multi_tweet,
    expected: [
      "Because I think it's the wrong way to think about them.\n\nProgrammers should be thinking about the abstraction first, trying to find the simplest possible 'contract' that solves the problem they are currently trying to solve.",
      "It's fine and appropriate for programmers to create a unique abstraction for each problem you face when writing a program. Some of those abstractions might be useful in subsequent projects, but you would only find that out when working on the next project.",
      "The words in that book, and the mindset of many C++ programmers in the 80's/90's, was to try to identify bits of code that look similar, and then produce a common set of abstractions that can be used across many projects.",
      "This led to a lot of wasted effort in trying to make the subtly wrong abstraction fit.\n" +
      "\n" +
      "tl:dr code re-use is a lie.",
      "I think Sandi Metz's talk + blog post on \"duplication is cheaper than the wrong abstraction\" also explains how 'wrong' thinking about abstraction can have a high cost: https://sandimetz.com/blog/2016/1/20/the-wrong-abstraction"
    ]
  },


  {
    input: multi_tweet_three,
    expected: [
      "It never seems either entirely appropriate or wise for a politician to comment on the sentences received by individuals. The reasons for the sentencing are here, if you're bold enough to comment on which part you think is wrong: https://twitter.com/BarristerSecret/status/1835673864614887851",

      "But if you are going to be speaking on how awful our legal system is, perhaps you should say something about the massive backlog in trials. Apparently serious crimes are taking multiple years to come to trial, and the backlog is getting worse: https://x.com/BarristerSecret/status/1833781105490534706",

      "I have a strong suspicion that the court backlog is going to block the governments ability to pass the Renters Rights Bill, as the property owners are going to raise a massive stink, and it's not clear that the courts would even be able to cope. https://centralhousinggroup.com/worse-court-delays-after-labour-renters-rights-bill/"
    ]
  }
]



describe("twitter_splitter", () => {
    test.each(cases)(
      'splits them correctly',
      (tweet_test) => {
          let result = split_tweets(tweet_test.input, Numbering.None);

          expect(result).toHaveLength(tweet_test.expected.length);
          let i = 0;
          try {
            for (i=0; i < result.length; i+=1) {
              expect(result[i]).toEqual(tweet_test.expected[i]);
            }
          }
          catch (e) {
            throw new Error(
               //"Error in split tweet " + i + "Expected: " + JSON.stringify(tweet_test.expected) + "\nbut have " + JSON.stringify(result)
               "Error in split tweet " + i + "\n" + e
            );
          }
      }
    );
});

