/*
Fetch a random manuscript image from davidhume.org, to display in the header at the top of the page.
*/
const pages = ['0/1', '0/2', '0/3', '1/5', '1/6', '1/7', '1/8', '1/9', '1/10', '1/11', '1/12', '1/13', '1/14', '2/17', '2/18', '2/19', '2/20', '2/21', '2/22', '2/23', '2/24', '2/25', '3/27', '3/28', '3/29', '3/30', '3/31', '4/33', '4/34', '4/35', '4/36', '4/37', '4/38', '5/41', '5/42', '5/43', '5/44', '6/45', '6/46', '6/47', '6/48', '7/49', '7/50', '7/51', '7/52', '8/53', '8/54', '8/55', '8/56', '9/57', '9/58', '9/59', '9/60', '10/61', '10/62', '10/63', '10/64', '10/65', '10/66', '10/67', '10/68', '11/69', '11/70', '11/71', '11/72', '11/73', '11/74', '11/75', '11/76', '12/77', '12/78', '12/79', '12/80', '12/81', '12/82', '12/83', '12/84', '12/85', '12/86', '12/87', '12/88']

const index = Math.floor(Math.random() * (pages.length - 1))

const url = `https://davidhume.org/assets/img/d/${pages[index]}.jpg`

window.fetch(url).then((response) => {
  document.querySelector('.header-image').style.backgroundImage = `url("${url}")`
  document.querySelector('.header-image').style.opacity = 1
})
