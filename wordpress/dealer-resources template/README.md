# Dealer Resources Template Build

## Details

- Built: October 2022
- Last Updated: October 2022
- Primary platform: Desktop
- Turnaround: ~4 Days

## Exposition

In late September 2022, I was tasked with building out a page template on our Training as a Service site _The Playbook_. The template would need to easily organize content created by our non-developer trainers while also presenting it to our dealer users in an easily digestible way. Additionally, the team had recently been provided with a mockup for a future design of the site [design-inspiration.jpg] and I was asked to build the new template in a way that would complement that design.

To start the process, I did a quick prototype of the template using Figma to quickly decide how the page might ultimately look [design-concept.jpg] and used icons from Iconify.Design's vast library of assets.

Once I'd done my prototyping, I built out a simple HTML skeleton and populated the data with static values. Then I began building in earnest and -- due to the complexity of the build -- decided to move most of my work into a class [class.php] that would then be accessed by the template [template.php] I would be building. As I worked, I continued my exploration and practicing of Object Oriented PHP and did my darndest to document the snot out of the code as I went in the hope that I may actually remember how things worked when I inevitably return to this project for updates in the future.

Once I got the code to a workable place, I began building out the styles I would need to make the page look appealing [specifics.css] while leveraging the existing global styles I'd built out to define various variables I use throughout the site [global.css]. Once I had the desktop view squared away, I began work on a mobile view that would retain the details from the desktop layout without taking up more space than was necessary on mobile.

Finally, I went back into the static content I'd laid out and, after building out a new Advanced Custom Fields group, went back into the code and replace the static values with dynamic ones that my trainers would be able to populate without digging into the code.

Ultimately, the page came together nicely and the outcomes are documented in [desktop-view.jpg], [tablet-view.jpg], and [mobile-view.jpg].

## Reflection

While the process I used worked quite well, I need to improve my impulse to start my design with desktop. I know mobile-first is the better way to build and design but I have fallen into a habit of going desktop first because based on our analytics a vast majority of our consumers interact with our site from a desktop browser.

I also need to continue to improve my command of Object Oriented PHP as it is something I've never been formally trained in but is an industry standard that I certainly need to master as I continue in this field.

My commenting also needs improvement as this is the first big build I've legitimately tried to use PHPDoc on.