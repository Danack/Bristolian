
import {split_tweets} from "./TwitterSplitterPanel";


let tests = [
    {
        input: "This is just over 280 characters. This is just over 280 characters. This is just over 280 characters. This is just over 280 characters. This is just over 280 characters. This is just over 280 characters. This is just over 280 characters. This is just over 280 characters. This is just over 280 characters.",
        output: [
            "This is just over 280 characters. This is just over 280 characters. This is just over 280 characters. This is just over 280 characters. This is just over 280 characters. This is just over 280 characters. This is just over 280 characters. This is just over 280 characters.",
            "This is just over 280 characters."
        ],
    }
]


describe(
 'twitter_splitter',
  function () {
    it('Message queue management', function () {

        let result = split_tweets(tests[0].input);

        expect(result).toHaveLength(tests[0].output);
        expect(result[0]).toEqual(tests[0].output[0]);
        expect(result[1]).toEqual(tests[0].output[1]);
    }
   );
 }
);