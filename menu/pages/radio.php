<style>
    .input-hidden {
  position: absolute;
  left: -9999px;
}

input[type=radio]:checked + label>img {
  border: 1px solid #fff;
  box-shadow: 0 0 3px 3px #090;
}

/* Stuff after this is only to make things more pretty */
input[type=radio] + label>img {
  border: 1px dashed #444;
  width: 150px;
  height: 150px;
  transition: 500ms all;
}

input[type=radio]:checked + label>img {
  transform: 
    rotateZ(-10deg) 
    rotateX(10deg);
}

/*
 | //lea.verou.me/css3patterns
 | Because white bgs are boring.
*/
html {
  background-color: #fff;
  background-size: 100% 1.2em;
  background-image: 
    linear-gradient(
      90deg, 
      transparent 79px, 
      #abced4 79px, 
      #abced4 81px, 
      transparent 81px
    ),
    linear-gradient(
      #eee .1em, 
      transparent .1em
    );
}
</style>

<input 
  type="radio" name="emotion" 
  id="sad" class="input-hidden" />
<label for="sad"> 
</label>

<input 
  type="radio" name="emotion"
  id="happy" class="input-hidden" />
<label for="happy">
  <img 
    src="//placekitten.com/151/151" 
    alt="I'm happy" />
</label>