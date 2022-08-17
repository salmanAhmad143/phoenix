const args = process.argv.slice(2);
const fs = require('fs');
const { AudioContext } = require('web-audio-api');
const context = new AudioContext();

context.decodeAudioData(fs.readFileSync(args[0]), (audiobuffer) => {
    console.log(audiobuffer.sampleRate)
    // choose your channelwa
    const channelData = audiobuffer.getChannelData(0);

    var filePath = args[0];
    filePath = filePath.substring(0, filePath.lastIndexOf('.'));
    console.log(args[0]);
    fs.writeFileSync(filePath + '.pcm', channelData);
});
