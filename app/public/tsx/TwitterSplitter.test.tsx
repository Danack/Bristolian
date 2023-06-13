

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

let cases: TweetTests[] = [
    {
        input: "This is just over 280 characters. This is just over 280 characters. This is just over 280 characters. This is just over 280 characters. This is just over 280 characters. This is just over 280 characters. This is just over 280 characters. This is just over 280 characters. This is just over 280 characters.",
        expected: [
            "This is just over 280 characters. This is just over 280 characters. This is just over 280 characters. This is just over 280 characters. This is just over 280 characters. This is just over 280 characters. This is just over 280 characters. This is just over 280 characters.",
            "This is just over 280 characters."
        ],
    },
    {
        input: exactly_280_characters +
            "\n" + "the newline should be cropped",
        expected: [
            exactly_280_characters,
            "the newline should be cropped",
        ],
    }
]



describe("twitter_splitter", () => {
    test.each(cases)(
      'splits them correctly',
      (tweet_test) => {
          let result = split_tweets(tweet_test.input, Numbering.None);
          // try {
            expect(result).toHaveLength(tweet_test.expected.length);
            expect(result[0]).toEqual(tweet_test.expected[0]);
            expect(result[1]).toEqual(tweet_test.expected[1]);
          // }
          // catch (e) {
          //   throw new Error(
          //     "Expected " + JSON.stringify(tweet_test.expected) + "\nbut have " + JSON.stringify(result)
          //   );
          // }
      }
    );
});

