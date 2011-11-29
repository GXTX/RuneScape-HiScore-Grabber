<?php
/*  RuneScape HiScore Grabber
 *  ------------------------------------------
 *  Author: wutno (#/g/tv - Rizon)
 *  Last update: 11/28/2011 1:42PM -5GMT (redo the whole class, implement mysqli)
 *
 *
 *  GNU License Agreement
 *  ---------------------
 *  This program is free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License version 2 as
 *  published by the Free Software Foundation.
 *
 *  This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  You should have received a copy of the GNU General Public License
 *  along with this program; if not, write to the Free Software
 *  Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA 02110-1301 USA
 *
 *  https://www.gnu.org/licenses/gpl-2.0.txt
 *
 *
 *
 *  SQL structure (NEEDED): 
 *  CREATE TABLE IF NOT EXISTS `highscores` (
 *     `player_name` varchar(25) NOT NULL,
 *     `data` text NOT NULL,
 *     `last_updated` int(11) NOT NULL
 *  ) ENGINE=MyISAM DEFAULT CHARSET=latin1;
 */

#Since we all enjoy Open Source 
if(isset($_GET['dat']) && $_GET['dat'] == "sauce"){
    $lines = implode(range(1, count(file(__FILE__))), '<br />');
    $content = highlight_file(__FILE__, TRUE);
    die('<html><head><title>Page Source For: '.__FILE__.'</title><style type="text/css">body {margin: 0px;margin-left: 5px;}.num {border-right: 1px solid;color: gray;float: left;font-family: monospace;font-size: 13px;margin-right: 6pt;padding-right: 6pt;text-align: right;}code {white-space: nowrap;}td {vertical-align: top;}</style></head><body><table><tr><td class="num"  style="border-left:thin; border-color:#000;">'.$lines.'</td><td class="content">'.$content.'</td></tr></table></body></html>');
}

if(isset($_GET['user'])){
	$user = ucfirst(strtolower($_GET['user']));
} 
else {
	die("Please set a username.");
}

$db = new mysqli('hostname', 'username', 'password', 'database');

class rs{
	public $username = '';
	private $skill_names = array('Overall','Attack','Defence','Strength','Constitution','Ranged','Prayer','Magic','Cooking','Woodcutting','Fletching','Fishing','Firemaking','Crafting','Smithing','Mining','Herblore','Agility','Thieving','Slayer','Farming','Runecraft','Hunter','Construction','Summoning','Dungeoneering','Duel Tournament','Bounty Hunters','Bounty Hunter Rogues','Fist of Guthix','Mobilising Armies','B.A Attackers','B.A Defenders','B.A Collectors','B.A Healers','Castle Wars Games','Conquest','Dominion Tower');
	private $image_links = array('data:image/gif;base64,R0lGODlhEAAQAIcAAAAAAP///wgICNEYGnYPEXYQEZcVGHsSE8dUVaYXG4QTGIQTF3YRFWoPElQMD00LDYoUGIMTF3oSFn8TF3IRFGMPElAMD08MDm8RFFsOEZopLGpAQcR3eqGFhksLDkQKDTcIC0gLDkAKDUQLDlQOEpQkKsBJT/zr7NEaKKUYJSsHCogdJbU6RFE1OHwWIndoauhMYtElQ+QxUNOPn8IhS6mdoYd+grujrm9iaV1YW8m0yZSPlMjEzHVyeREXQUZSpi4/nzA/kzE1TBojTygtRCQsP0JHURgfKz5KXSsxO4CMnR8oNCgyQCo0QUlWaCs2RDRBUSw3RD5MXUBOX0dWaUlYa11rfbzP5nJ7hiY0RB8qNh8oMi05RzdFVTZEU0dYazE9Skpbbk1ecVxvhTtHVVRleG2DmyY2RyQyQD5OX0NUZU5hdHWFlhgjLS9CUyUxPDlJWD5OXWuCmIuapr7W50VTXL7a677Y6b3X6LvV5r3Y6HaDikJMUGyDi6S5wWmFii03OF1pY3SDe32Ngz1FQHKAd0FKREdQSnuKgISXiY6ikqKtpG+EchpZISJnKzN5PHCpdw9JFBtcIiJjKCpvMS1yM0aPTUSHS16hZQo9DhVSGj2FQ1pxW3mPeqi6qWl1aMPFvv/3cf/eIf/nWP/oaP/XBP/aKP/dO//pef/pif/aTvm4Av/IJd+2RP/VV//FOee1O/+1CP+2GP+8J9moOP/JTP/LXP/QY//WfbqTQcabRuCxVf/NZf/+/P+hAP+oFP/Nfv/Ujf+2SPSOBt6BC9d/FP+nN//Dd//FfM9vAJNNAP+pR7pkE/+LIP+2df+gVaFDAMlPAKM+AOhlFc5dFZNSKdpTD9dZFfV7PB0QCsNMGRgHAXgqDNFbL29OQkgUBOBQHug2AM82DSsSC7tWOKNONVgUAzkVDGMVBO5QLNg+IJ0sF2wOAKU9M+ZjV7JDPN3JyKc3M7BYVFAMDJ4nJ6ErKqEuLsNFRM5eXsi0tO7a2pyUlJaOjv/+/gEBAf///yH5BAMAAP8ALAAAAAAQABAAAAitAP8JHEiwoEGBAhImPDhQACRIni4JYPhPgKeLlyRSFIAJU0SNBRUKsGRJUaVKEwkKuHcP38iSlRylRIivZj0BmzYlciRT5bt3COjh1OlI0syKP4MKePSokySjKuPFM1FiaVNJmkRWlEpVACVKjDRlBQLkR0J79kys8Ao264+3Q86mXTtp0qdIAoQI+RFXgAYNLNY2asQpkwAiRIL0LVGChQt/gwsLKFIkiA8BAQEAOw==','data:image/gif;base64,R0lGODlhEAAQALMAAEY8L7u5A3huYVpQQ1BGOWRaTYJ4a3d7CXd3dwAAAP///wAAAAAAAAAAAAAAAAAAACH5BAEAAAoALAAAAAAQABAAAAQ+UMlJZbqp6otQ1hPXfaDokZV5gQpnnCwnwGWCFPRmD3mIIYRe6HAIHIS+YmAVAwCIqFTCmThEKYkl5urjaiIAOw==','data:image/gif;base64,R0lGODlhEAAQAKIAAFtbVZCQkHd3dwAAAP///wAAAAAAAAAAACH5BAEAAAQALAAAAAAQABAAAANBSLrcOzBKyAa4IovAR8Xa1n1ABomB94Anl5LmgKqKNUXvekN5zcouGsFSErRGq+KxN7TERJSKU5MSPqZVx1XSSAAAOw==','data:image/gif;base64,R0lGODlhDQAQAKIFAEY8L9CyhAAAAQAAAKGSev///wAAAAAAACH5BAEAAAUALAAAAAANABAAAANEWFois1C10BwcjRAQgJXBBnCchYXaSDoCqqUb1YovEE+baNvOGbgjgkDhe2kai5breFEahxCnhhKR/qDJn5Z6GXi/0AQAOw==','data:image/gif;base64,R0lGODlhEAAQAKIFAHo9CbM5AwAAAQAAAN1PAf///wAAAAAAACH5BAEAAAUALAAAAAAQABAAAANEWCUso6MJtQa5joUAxmNX6BEb0IFhepWn6nKtm8KRPJu1TdKazDILQeDFASo0m5XJSEFuihNKU2BaRqVTK3Yr2Xo93gQAOw==','data:image/gif;base64,R0lGODlhEgASAKIGAAlsAjUvG3V7fHo9CQAAAQAAAP///wAAACH5BAEAAAYALAAAAAASABIAAANoaLrcW6QYSGp0hoyoh9+SpXTd53GbUkDCFkGnUDGFIKyqF1x0LUuQwEnSs60Kuw/v4SMEjh1io7YjFjzSyTFgBGIfBADg2YQNpAVxAHA0RheVNVtrGzIqAN7KtoTjmBp9GA9fg1NHBgkAOw==','data:image/gif;base64,R0lGODlhDwAPAMQdALfOu83n0JayoarCsLPLuJm1o6zDsb/Xw87o0RMTDa/Es6jArp25p7vRvZWwnqG2pcTcx9Dq0rrSvcHYxLDItM/p0rjPu8vkzsTex9bx2Ke9rMbgygAAAP///wAAAAAAACH5BAEAAB0ALAAAAAAPAA8AAAVeYCeOHUeeJHeZaFpcFNtynHAN9Fw7l7HkqVqBgThYfjKhgxHJXA6GAa4UqAYqmewGw8WYOAQCAILIYBqatCyRAEwgm4biARzRAICNhi4LEvR1KBwSGwp9MxuHLSUtIQA7','data:image/gif;base64,R0lGODlhEAAPAKIGAK6oBXo9CQAG/gAAAQAAABsGkv///wAAACH5BAEAAAYALAAAAAAQAA8AAANJaLrT+3AQQQiEZJQixl3ZtnmfQYijdTWj1n1aIW2d+jQsTZWx+D6VEKqQyZhigaQSEBBQKsMlwOkMDjnUqkdy8lEzJAVu3LAlAAA7','data:image/gif;base64,R0lGODlhDQAQAKIFAPf33aaMEHtnCV1QEQAAAP///wAAAAAAACH5BAEAAAUALAAAAAANABAAAAM7WEWs2xAQR6R7M7fLO/vgRgzkIARoMC2k4J7o+rSxzJhvPeO5ercvmOAHDA4JglLR9VE6ISPnYIMJrRIAOw==','data:image/gif;base64,R0lGODlhDwAQAKIHAHo9CQAAASocB1g6DACpAAlsAgAAAP///yH5BAEAAAcALAAAAAAPABAAAANSeHoW/mYpQwu5ly5KisVZxHkdGBoe2X3YmK4W6aaDkNoUWgxe3eMMQ2dg8AV4muBuwBQEgBuUQFADFJLR4gCQk0gMAG7EG61hyQbqdkwO5tiHBAA7','data:image/gif;base64,R0lGODlhEAAQAKIAAIQuCCR7CT8uCwAAAP///wAAAAAAAAAAACH5BAEAAAQALAAAAAAQABAAAAM7SLo7zrCNMGKc9Fk1RcjVNXhgyDiCo6YQ+rgmAcujxtXVLKU5fvq6G29l4wBUH57IkaRsbpjYRiVVJAAAOw==','data:image/gif;base64,R0lGODlhEQARAKIGACUlJUY8L1VbWnV7fAAAAAAAAf///wAAACH5BAEAAAYALAAAAAARABEAAANXaGpE9aWtCYO9MK5iM79CsUFjVIRTujQEIKoQO8za5MxDgc9Eqg8CAe7XowRPQKCwuHoIg8FcI4ZUhiI9wlBo1b0Yv1loOrb9rgovDMlkqcAC5pvymCsSADs=','data:image/gif;base64,R0lGODlhEAAQAKIAALM5A91PAa6oBVg6DAAAAP///wAAAAAAACH5BAEAAAUALAAAAAAQABAAAANQWLrUvbA4IuqETeSg6VNU0ATkqIFhFQCcwD0hUAH0/EoCTdJsflO6XG0lI0hChAFBpHwZj8mBUqqETRrU6iSr5VazVqzUMX4yosZLBGpeuxMAOw==','data:image/gif;base64,R0lGODlhDwAQAKIEAHJCClcxC3d3dwAAAP///wAAAAAAAAAAACH5BAEAAAQALAAAAAAPABAAAANCSLo6znAN8ZobbFJMRghVJ4yP81XOuHUeeGmkdQ4AkFJSS794Plsclu0HcdRuIaHtQuIYl5Zm5xhkxXZVIOYSiSQAADs=','data:image/gif;base64,R0lGODlhEAAQAKIAAFVbWjUvG3V7fAAAAQAAAP///wAAAAAAACH5BAEAAAUALAAAAAAQABAAAANQOFrc3kQMQpSdLsohuseMNgSeF4AREARAKbBYurYlK1HvDOwtu1OqEW/3slBWipXyRIAwK0tQaAH1NR9OnTQz8nW2BSivtO3qaJIMZc2mMBIAOw==','data:image/gif;base64,R0lGODlhEAAQAKIFAFVbWjUvG0Y8L3V7fAAAAP///wAAAAAAACH5BAEAAAUALAAAAAAQABAAAANFWErcpBCyQaljkoJtiQjPcgFVAEYRww0YKpqdWzDCR7Kh5H2qle6ghk9UCy5IIRovR0DOgK0joFGMHqlLV2NmssocMlQCADs=','data:image/gif;base64,R0lGODlhEAAQAKIAAADvAAB7AADVAACpAABmAAAAAP///wAAACH5BAEAAAYALAAAAAAQABAAAANAaLrc/jDKQiO95FZWBiUg5Sna4IGZqQHXgILeKgjsmwHzNZu2mRuFnevlExSAlF2AEBjkNkjTshlzaK6SrFaRAAA7','data:image/gif;base64,R0lGODlhEAAQAJEAAFZLOgAAAP///wAAACH5BAEAAAIALAAAAAAQABAAAAI2lB0Zxx0AVmMKqikS3FFJzW3aZ3GPiJxnZ0nZo3puGmMH2NlZmGDg2qMAf0JRJUIxDoUyD6MAADs=','data:image/gif;base64,R0lGODlhEAAQAIMAAAAAAP///x8fH////wLoLxTQwALoL478uP6/Z478uBJ+0N17wP7BpwAAAKkS99jAfCH5BAMAAAEALAAAAAAQABAAAAQsMMhJq7046x2A/yAFjmNHCmQ4ASggehULv67EtrU5Cnzqgjyc6oXjzDhITgQAOw==','data:image/gif;base64,R0lGODlhEAAQAOYAALqmm5F+c2dmZxcTELWspmZZUV5dFHNzc7y9vcy3qklDO6usrHhqYtzX0ywoJmhfWJaUBE5OTnp6eri2BTIsKzozK01MFv7+/kM6NEU9OOvi3eXc1ltbW8rKytbEuezk4EA3K7GYic7OztC/s6CgoENDQ0lISMCpm/T09J2Ylq2WidPRz9DLyIOCDcG3sZqGeoqEFj8/QJ6cIvXv66eoqD82MaOPg83LAqemAsbHx/r6+ezq6O/p5eLVzkpAOk9PDl9SSUE1Nkw/N7ufjVZMRlFRUVpYUC0tACAfHT84H97SytvMw4eGAYqJAQAAAP///wAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAACH5BAEAAE8ALAAAAAAQABAAAAe/gE+Cgk6FhoaDiU4WTT9IFSADTomDixATP0GRk5RPhRktEzBJhYqHSEYyNxClhE4OsEgRAg8GOKyuQAAuDCUSByYDR0ycTgUjHylIBxICJiMPSMUJHx4+MQcHRTk0DQrFGxc6BBQCHCIIDixCpU4nGx8ABCIkCx0cKwwRhipDIzMXDlDIgGDBASUMTBQK8OIEjws0SpBAcSCABgIZFgYI4eGCxw1LOjTY8UDSwhchemhI8CIAABsFfLQ7RPPQk0AAOw==','data:image/gif;base64,R0lGODlhEAAQANUAAAAAAP///1tpxlRht1xqx1xpxlxqxlVit1tqxj84Lp6QfXFnWnBmWWFWSFdOQ5KEc56PfXhtX7CgjKaXhIN3aHRpXJ6Qfo+Ccm5kWLCgjXBmWoR3aIZ5anhsX5KEdJGDc56Pfo+Bcot+b3hrXoR2aJCBcot9b4Z5bP///wAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAACH5BAEAACgALAAAAAAQABAAAAaHQJRwiAIYAcSkEHBqnpDKZdOIeRKNUigAg9FOtyPtqLvkUgkCRMGAIBcxCepoMDgMRosK0uhwbEkKGRMhJwwNHVp9GBcPEiAhGAwADYkOGBQQEhMnJl2UQwCWFBaanRqTUEWWFx4SEBwikp+gGBsggh9cDRGpbyUeGSAnuVhJW1zIvcZHxUJBADs=','data:image/gif;base64,R0lGODlhEAAQAKIAALA5BIs3CZCQkFlaVXd3dwAAAQAAAP///yH5BAEAAAcALAAAAAAQABAAAANUeLpnZSwuYwCVsYBdcKOFFlLQVAhCGJwE8SgOmhZr276ncIeFfce2QM3nYtlUIdsApPPReoNop+dcFaLSxpUwoAWw0pJD+sS+JiDR44LRcDxoUiQBADs=','data:image/gif;base64,R0lGODlhEAAQAMQAACsbHCgaGyocHS4fIi4gIy8hJC8iJjYuMCodIjAjKCkdIi4iJzEmKjAmKjUoLzotNDonNDUtMzcqNC0oKyMcIwAAABUbEiYrHCAhEyorHxkZGf///wAAAAAAAAAAAAAAACH5BAEAABsALAAAAAAQABAAAAVG4CaOHDmeZFByKko+zwqXLjdvdnzSuayLLF4rCMT9ODwh8uYyxnpNJzLQwiGVQCF1SNsFYMzaF/yooljkcte73a531ysqBAA7','data:image/gif;base64,R0lGODlhEAAQAIMAAAAAAP///75jINRzLPmMPGIzEd/f38jIyLCwsJmZmX19fWZmZv///wAAAAAAAAAAACH5BAMAAAEALAAAAAAQABAAAARHMMhJq704awm6/2BHjGNnnCiCKINQAEN3zHNiL+0bA/Rh3zmYrPZLLHACIc9XBCZ3PkBRihtYOwlAQJrdHr+eSYcD2pjP5wgAOw==','data:image/gif;base64,R0lGODlhEQAQAIcAAAAAAP///wMCAw4MDhUTFRoZGiIhImdmZxwbH4B9iU9NVqOhqkxLUN7c5uTi7jg3QHt6guLh6oOCjXx7hZiXo6SjrsHAzKmostXU3+zr9ZuaqNTT5MzL3MvK28zL2wMDBAkJCgwMDUtLUcrK2a2tt8/Q4D0+SVJTYkNET1JUZSwtNp6fqR4fJb/Bzp2hs7u/0eDj7yQmLTw/SmhqcVBVZm1zh0hMWd7i7woLDi8yO6ivwmpueN/m9+Lm8ImSpnyElKWuwYuTo8XM2j1EUUxcdVZgcA8RFJurwrbF24+aq8fS41hpf26AmJurwUNJUUdWaYumyIKbuoulxoulxXqOp1BdbZivy6O51DA+TlVshWiBniUuOE5gdXiTsiMrNHeSsHiTsJKy1Y6sz4upy3iRroypymyDnI+szYqmxpe114ikwnmRrV5xhh8lLHeOp2FziJOtypWuyZixzJ620S82PqO3zZWmuWhudRgfJniavXaWtmiDn2iDnnSRr3OPrEdYaoWiwIqnxYaivo6pxB8kKU9ZY6/A0RkiKklieR4oMUdbbTdFUm+LpVJld4unwhUbIGh9j3ODkBIXGwYJCwgLDQoLCgEAAAMDAwICAgEBAf///wAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAACH5BAEAAAEALAAAAAARABAAAAjJAAMIHEhwIAAABRMOnHTnAEKFBAFE6oHhocKDlSApySDh4EUnb8xccZAAwCOLAQBUImTHypAqPEgAwEPk4UEVJ9CgyTEgyA02jfRgQThTBgoFaKIsSeKgAZk8Zh4akfHABpUzc9IgcSAEkBlCFg8SKOImTpw6huSEWYRSIIIUf8wIQlPmDJgtbRFU0JACERm6jJ7EQGlgRYcKNcwAGQSFS5ZEYWeU6HAhiIsWTfqA+XIoLAURBwFM6ADaC5pCFgdUCusxgKRKAwMCADs=','data:image/gif;base64,R0lGODlhEAAQAPcfAPbfAdKqJWZKAM2ZAPLpXKKYDtOhAHdnAKGQGUAzA5CHCPLWNtKoDIBhAGdsAIlaAIp7Euzoyp57AEULAJuPIIZhANzCAqitr+3pyxgSAGNQAHMdAKiCAwsKCAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAACH5BAEAAB8ALAAAAAAQABAAAAifAD8IHEiwoMF/CP91MDiwQwcAHDJgiODQYAcOEBMK0MBhYUMOBTRkuPAPg0kMHQf+06DBIckLGzag9Phh5cIOF0ZOwJABAAGPNj90yDAyJwSfQFN2qCCAKAcLFjYEWPgvpVANDxpICBAzwL+aTz06dLggJoevHRyEJdiBgVmxCihYoNnBQEyaQjmEdZhgwAa8AjscQKCX5QDAKhMixBsQADs=','data:image/gif;base64,R0lGODlhEQATAMQaAL3Y6GmFimpAQaGhob7W577Y6SwZBhwQBbvV5n95gb7X52mGirzP5r7a6/Lz822DinCGjkNDQ/b09G9iaUMmC2A5E2yDi0JCQv///wAAAP///wAAAAAAAAAAAAAAAAAAACH5BAEAABoALAAAAAARABMAAAWOoCZqWTaK5Tlml3my7nphMYq1MnaX/LzbmAZmYKAYBhiEzsTCECwTDGWKmQQKu4wOYIFSKpSJJaCkabldQWUtGFsAZmfXoma7sSVdoTtZVyYPC3BmM09RfhIJEHA4WhgMQ18UAw4KSyg+LQcVBxkROjiYNCQUTD8vjaUkoSopGaopKq2qsrUZBjW1K7EqIQA7','data:image/gif;base64,R0lGODlhEAAQAIcAAAAAAP///wgICNEYGnYPEXYQEZcVGHsSE8dUVaYXG4QTGIQTF3YRFWoPElQMD00LDYoUGIMTF3oSFn8TF3IRFGMPElAMD08MDm8RFFsOEZopLGpAQcR3eqGFhksLDkQKDTcIC0gLDkAKDUQLDlQOEpQkKsBJT/zr7NEaKKUYJSsHCogdJbU6RFE1OHwWIndoauhMYtElQ+QxUNOPn8IhS6mdoYd+grujrm9iaV1YW8m0yZSPlMjEzHVyeREXQUZSpi4/nzA/kzE1TBojTygtRCQsP0JHURgfKz5KXSsxO4CMnR8oNCgyQCo0QUlWaCs2RDRBUSw3RD5MXUBOX0dWaUlYa11rfbzP5nJ7hiY0RB8qNh8oMi05RzdFVTZEU0dYazE9Skpbbk1ecVxvhTtHVVRleG2DmyY2RyQyQD5OX0NUZU5hdHWFlhgjLS9CUyUxPDlJWD5OXWuCmIuapr7W50VTXL7a677Y6b3X6LvV5r3Y6HaDikJMUGyDi6S5wWmFii03OF1pY3SDe32Ngz1FQHKAd0FKREdQSnuKgISXiY6ikqKtpG+EchpZISJnKzN5PHCpdw9JFBtcIiJjKCpvMS1yM0aPTUSHS16hZQo9DhVSGj2FQ1pxW3mPeqi6qWl1aMPFvv/3cf/eIf/nWP/oaP/XBP/aKP/dO//pef/pif/aTvm4Av/IJd+2RP/VV//FOee1O/+1CP+2GP+8J9moOP/JTP/LXP/QY//WfbqTQcabRuCxVf/NZf/+/P+hAP+oFP/Nfv/Ujf+2SPSOBt6BC9d/FP+nN//Dd//FfM9vAJNNAP+pR7pkE/+LIP+2df+gVaFDAMlPAKM+AOhlFc5dFZNSKdpTD9dZFfV7PB0QCsNMGRgHAXgqDNFbL29OQkgUBOBQHug2AM82DSsSC7tWOKNONVgUAzkVDGMVBO5QLNg+IJ0sF2wOAKU9M+ZjV7JDPN3JyKc3M7BYVFAMDJ4nJ6ErKqEuLsNFRM5eXsi0tO7a2pyUlJaOjv/+/gEBAf///yH5BAMAAP8ALAAAAAAQABAAAAjAAP8JHEiwoMF/AAAgVHhQIAA5awBICcPwIAAzY9KooWgRQBkzZtSIEYMkIUEAcVJWMaOkTBwuT5owBADHi0ozPMpAyZJlyRKFAKKAgdIlDpsTVrI02XJEpkMqX9S4mdMPSxYwXJjMrDLFyhcnPHrtOJOGzJsuCcFMQdMjn75++uDVsJGjzZOEU6a8McJvESh4+z7lSOIT75uEdQQBSATAEKCEWhIeRsgH0SBFgQ49XohwIIBBCRtXrOiwEFBCpAMCADs=','data:image/gif;base64,R0lGODlhEAAQAIcAAAAAAP///wgICNEYGnYPEXYQEZcVGHsSE8dUVaYXG4QTGIQTF3YRFWoPElQMD00LDYoUGIMTF3oSFn8TF3IRFGMPElAMD08MDm8RFFsOEZopLGpAQcR3eqGFhksLDkQKDTcIC0gLDkAKDUQLDlQOEpQkKsBJT/zr7NEaKKUYJSsHCogdJbU6RFE1OHwWIndoauhMYtElQ+QxUNOPn8IhS6mdoYd+grujrm9iaV1YW8m0yZSPlMjEzHVyeREXQUZSpi4/nzA/kzE1TBojTygtRCQsP0JHURgfKz5KXSsxO4CMnR8oNCgyQCo0QUlWaCs2RDRBUSw3RD5MXUBOX0dWaUlYa11rfbzP5nJ7hiY0RB8qNh8oMi05RzdFVTZEU0dYazE9Skpbbk1ecVxvhTtHVVRleG2DmyY2RyQyQD5OX0NUZU5hdHWFlhgjLS9CUyUxPDlJWD5OXWuCmIuapr7W50VTXL7a677Y6b3X6LvV5r3Y6HaDikJMUGyDi6S5wWmFii03OF1pY3SDe32Ngz1FQHKAd0FKREdQSnuKgISXiY6ikqKtpG+EchpZISJnKzN5PHCpdw9JFBtcIiJjKCpvMS1yM0aPTUSHS16hZQo9DhVSGj2FQ1pxW3mPeqi6qWl1aMPFvv/3cf/eIf/nWP/oaP/XBP/aKP/dO//pef/pif/aTvm4Av/IJd+2RP/VV//FOee1O/+1CP+2GP+8J9moOP/JTP/LXP/QY//WfbqTQcabRuCxVf/NZf/+/P+hAP+oFP/Nfv/Ujf+2SPSOBt6BC9d/FP+nN//Dd//FfM9vAJNNAP+pR7pkE/+LIP+2df+gVaFDAMlPAKM+AOhlFc5dFZNSKdpTD9dZFfV7PB0QCsNMGRgHAXgqDNFbL29OQkgUBOBQHug2AM82DSsSC7tWOKNONVgUAzkVDGMVBO5QLNg+IJ0sF2wOAKU9M+ZjV7JDPN3JyKc3M7BYVFAMDJ4nJ6ErKqEuLsNFRM5eXsi0tO7a2pyUlJaOjv/+/gEBAf///yH5BAMAAP8ALAAAAAAQABAAAAi7AP8JHEiwoMF/AAAgVHhQIAAaEABgWMDw4MMUGBgoqEgwoQEaNCRAgIAhYUcMKCPQkGEAg4UHHhgCqFAhJQ0eBjKMGAEChEIAFzJkqBnjRIIRHkCoiOlwwgQJJGD0QzHCgYUQMiNQSDAhAo9eO0ZgaCCiQsIMFEQMyKevnz54NWwYWJqQQloC/BaBgrfvU44HPet+SHhAEIBEAAzNS+gTgIifBRANUhTo0OKFCAcCGJQwcUWOCAv9JMQxIAA7','data:image/gif;base64,R0lGODlhEAAQALMAAAAAAP///wEGBzHE40Xv2eLi4sjIyICAgP///wAAAAAAAAAAAAAAAAAAAAAAAAAAACH5BAEAAAgALAAAAAAQABAAAAReEMlBh7z0ogE6sFMHckdpfBtQfqR5EgMBFAerHoZRAEQ/7xRVrrDr+VicYdFjzHCYvxdsYpQRczUQ1VrA1qYb48zLAz99hpWvIiwzZaegqRyDHwSWJ3ITRE0qWnwgEQA7','data:image/gif;base64,R0lGODlhEAAQAKIHAP/xWc6/EnOJ+S1CrUBf/OXYAQ8tx////yH5BAEAAAcALAAAAAAQABAAAANMeLrcbDDKZoS9d5gnACAeCGgcUBDFWYzbUqUwTLpCEKfBrFSEDQcE3YGHghGCrZ3gGDsKiT9ncrjsBa5SzvGKRT4G4LB4OpSYHWhHAgA7','data:image/gif;base64,R0lGODlhEwATAIQVAB8AAD4AAE4AAGIAACAaAHoAAJkAACMjIzMpAE5OTmJiYpt8AIGBgbWRAJmZmcyjANarAK2trb29vf/MAMzMzP///////////////////////////////////////////yH5BAEKAB8ALAAAAAATABMAAAVy4CeOZPkdqKmeh5Mca9m6ChyLh0Tbd04litrt5IgkIg7eiEAgtRKMpIkwmTSJQOHUitspSVQEancLO8jlKgKhHVIhbNh1BRggGo01YTEvEQwCAQR4C4V9IgAAEwgDAB9MkCoABhMPhzGTBY5DI4mJnCMhADs=','data:image/gif;base64,R0lGODlhEwATAIQWAAIOEgAQFQIWGwAZIQAfKQUuOgY6SAhIWgBOaApacQBiggxwjQB6owCZzH2NkTOt1ly93pywtr29vczr9dbu997y+P///////////////////////////////////////yH5BAEKAB8ALAAAAAATABMAAAWe4DB8ZGmeQSAgwkCcZioUKqIwDaETQt8nBwNtYMM1Gg/IYrkECgOfwG238/WC0KhiMOlSKpFw2AEwCEhSrmVtkbjdZDN6O7EgIZIFUBI/a9VIbRJ7fXOAa28HfGV+aXVsb3CMJAJ0bHgJb4WUDASPSpkSEQEHWSUEOY8LbhECCX4mqDlfoq16sLGoOq0CCz0wsUm9vsAwOr64xSbJJiEAOw==','data:image/gif;base64,R0lGODlhEwATAOMJADMpAKOCAMyjAP/MAP/WM//eXP/ql//urP/xvf///////////////////////////yH5BAEKAA8ALAAAAAATABMAAARd8MkpgaUYW8FvpgA3jN03hWMqAOaDEkQ6sh96FHFKa8JQIIccaXfqHRAG4WDFGxCOSZkgQHw9YVIqqOeEKadVriwbHkvBW/EZnR5z2Om3HB6XB7Qmy33vabksfRQRADs=','data:image/gif;base64,R0lGODlhEwATAMIGAGIAAHoAAJkAADMzM6Ojo8zMzP///////yH5BAEKAAcALAAAAAATABMAAANveLo37iyqV2p9stli+h7RUHVAWRLGtYgcGbwBmhag1rlCLqcgexuAQE6wu4iAJuHQBCDQjsHXMAeLPTnBqVZ3BSq3Q6cD+wUTrwWCmpDVrVXHW5t4o01GOJ3Hfm/NUSoMLGlrToEhYx8QGSsPNREJADs=','data:image/gif;base64,R0lGODlhEwATAPeFAHN0cSslJrm4toaHjIaHgzg7P2JaQyYkJyQmJ2dmS2RsY21mRm9wVB8eG0hMUl1bQk9PQE9MPBgYHjAtKouNjG5rSk5LOW1nR2FfTExQVE1OREVEPCwtMm1lRWBfSG5xVDM0MmNcP2JpYEJHTS0uKyoqLjs3MGljQmtkRktQSiIkJklNU2ZfQ1dPPVJLO0xPVrGxr15YP11UPkhNWoiLcm9tTFVUPk5PQnFpSHJ6Zzk2Ln2DhXBsSjxAPJCRe3FwUoGGdHBmRmxjRExQVSotKm1oR3BzWW1pSCUkH0xQUSosKF9cQ2dnSo2NjFNbUyYkIX9/fE5HN2lmS2tmR15dS5SVlG9wUGVkTElLTm5oR01TTDM5PCAhJigpJiYoLDc1L21uUEVAMoWHbXV5XzY0L3l3ckZLR2hmR3FoSGtuVkJGSoiIh2plSmhkSGtvV2NaQ56enWpxWU5VTmtjRVFPPW5mSGlpUVVXUIaHhV5WQ2dlTz07NWtoSEhFOnFnSFlbUGZlS1dXS3JvTVJVVFlfWf///wAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAACH5BAEAAIUALAAAAAATABMAAAjuAAsJBEAAzxoKcATAqNIEShkAAiMWAjBiixktThSIkJOiBwKIEicm0YCBSZwcY8BcCQQCpEQAgwQIoJIGiBg7Mku4jAjgRYABAgjR8CFgQAAiOwcOgfCz6A6jNkgknZiBjoemRq08UDIVwIoIJ/gEkPnnzJIuXR1YCFG06Z0bKrpi2dD2Q9MZErqqGVAggB4jbgIUEMClawGjLKQw+CHkp5euHALM6TClhqAsQQI86XogygIUbSrwKIIjRoOphQgEkOHnQgJAdd6QIRBSIoEJLdAcYZPHBO3atr+EMeBCx2/gtvsg2XMcuW0CzSUGBAA7','data:image/gif;base64,R0lGODlhEAAQANU2AAAAAP7+/vr6+ubm5tjY2P///ykpKf39/fb29hMTE87Ozpubm/v7+/f397e3t6CgoN7e3qmpqefn5/Pz81VVVYiIiJWVlSUlJZeXl3JycsjIyM/PzwcHBwYGBmJiYvj4+KWlpVhYWAoKCp2dndLS0u3t7ePj4y4uLk9PT6SkpAEBAVNTU5mZmQQEBOvr62traywsLLW1tWpqavHx8VJSUqampgAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAACH5BAEAADYALAAAAAAQABAAAAZuQJtwSCwaj8ikcmkDOJ/QIqCTUrwArZECcwIMARwSAjEAAS4C0wLmbVYKgXigBvA0XLE2YFMoACwCHzQAAiUEegkHDCsiEAdxAhIPegAZMxoqBg4DEwQEBm1NCREDMgAhFAMsKKFCUK+tTLKzSEEAOw==','data:image/gif;base64,R0lGODlhEwATAMZ6AAEBAQIBAQMCAQUCAgUDAgUDAwYDAgYEAgYFBQkGBQgHBgkHBAoHBQoICAsIBgwIBgoJCgoKCgwKCg0LCg8LCA4MDA8MCw0NDQ4NDxENChENCw8ODRAODREODQ8PEBQODBMPChMPDhUPCxIQEhUQDBQRDxURERMSExUSExYSEhYTERYTExcTEhcTExgTExkTExYUFRcUFBgUExYVFRYVFhkVExkVFBYWFxcWFhcWFxcWGBgWFhkWFxsWFBsWFRsWFhsXFBwXFh0XFB4YFR4YFhsZGR4ZFx8ZFxsaGx4aFx4aGB4bHSAbGB0cHCAcGiMcGyAdHSEdHCEeHyMeGyAfISQfHCQfHiMgHycgHSQhICMhJCUhISYhISchIiQiISUiIiQiJSUiJSkiHyUjJSkjIiwkICgmKC8mHCgnJy0mJCgnKSwoKC0sLTUrIi4sLTMsKDAtLTQvLDMxMDkwLDg0MTU1Nzk4OTs5Nz48PUA8Pf///////////////////////yH5BAEKAH8ALAAAAAATABMAAAfFgH+Cg4Iyf1CEiYp/Nn9fi5ByDH9wH5CJcUUUf2tdIpeCdGgHg24mIJd3TQSJbDwPi3YVBotmSwiJeBsDan9pT2Vvc395YzoFhHUTvScuL0xiUyx/aloAhGoTJSgnNC5HWD4sP2BU14M5ERIQETctRFMpDT9hFwGEQB4YCX8nMUFGLPwZIUHAIg5/vMzY4cNHBy5/FIDKggRHDxU/NID640TKAhg1QpBZkQFUFBhnkgwR0maLA1BKiiSyQmLjlURVNuoUFAgAOw%3D%3D');
	
	public function check_vdb_user($data){
		global $db;
		$query = $db->query("SELECT `player_name` FROM `highscores` WHERE `player_name` ='".$this->username."'");
		if($query->num_rows != 0){
			$out = $this->grab_old_info();
			if(isset($_GET['update']) && $_GET['update'] == "1"){ //should we update the info?
				$this->update_user($data);
			}
			return $out;
		}
		else{
			$this->insert_user($data);
		}
	}
	private function update_user($data){
		global $db;
		$db->query("UPDATE `highscores` SET `data` ='".$data."', `last_updated` ='".time()."' WHERE player_name = '".$this->username."'");
		return true;
	}
	
	private function insert_user($data){
		global $db;
		$db->query("INSERT INTO `highscores` (`player_name` ,`data` ,`last_updated`) VALUES ('".$this->username."', '".$data."', '".time()."')");
		return true;
	}
	
	private function grab_old_info(){
		global $db;
		$query = $db->query("SELECT * FROM `highscores` WHERE `player_name` ='".$this->username."'");
		return $query->fetch_array();
	}
	
	public function grab_new_info($username){
		global $db;
		$get_page_info = curl_init("http://services.runescape.com/m=hiscore/index_lite.ws?player=".$username);
		curl_setopt($get_page_info, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows NT 6.1; Win64; x64; rv:11.0a1) Gecko/20111122 Firefox/11.0a1");
		curl_setopt($get_page_info, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($get_page_info, CURLOPT_BINARYTRANSFER, true);
		curl_setopt($get_page_info, CURLOPT_FOLLOWLOCATION, true);
		$page_info = curl_exec($get_page_info);
		$curl_info = curl_getinfo($get_page_info);
		if($curl_info['http_code'] == 200){
			$this->username = $db->real_escape_string($username);
			curl_close($get_page_info);
			return $page_info;
		}
	}
	
	private function compare($new, $old){
		$i = 0;
		foreach ($new as $new_fe){
			if($i != 38){ //only 37 skills
				$new_info = explode(",", $new_fe); //[0][1]([2])
				$old_info = explode(",", $old[$i]); //[0][1]([2])
				
				//find the differences
				$diff_0 = $new_info[0] - $old_info[0]; //rank
				$diff_1 = $new_info[1] - $old_info[1]; //level
				if(isset($new_info[2])){ $diff_2 = $new_info[2] - $old_info[2]; } //exp, minigames stop at [1]
				//
				
				//RANK
				if($diff_0 == 0 && $new_info[0] != '-1'){ $pt1 = number_format($new_info[0]); } //no difference
				else if($new_info[0] == '-1'){ $pt1 = $new_info[0]; } //isn't ranked
				else if($diff_0 < 0){ $pt1 = number_format($new_info[0]).' <font style="color:green;">&uarr; +'.number_format(abs($diff_0)).'</font>'; } //up
				else{ $pt1= number_format($new_info[0]).' <font style="color:red;">&darr; -'.number_format($diff_0).'</font>'; } //down
				//
				
				//LEVEL
				if($diff_1 == 0 && $new_info[1] != '-1'){ $pt2 = number_format($new_info[1]); }
				else if($new_info[1] == '-1'){ $pt2 = $new_info[1]; }
				else if($diff_1 > 0){ $pt2 = number_format($new_info[1]).' <font style="color:green;">&uarr; +'.number_format(abs($diff_1)).'</font>'; }
				else{ $pt2= number_format($new_info[1]).' <font style="color:red;">&darr; -'.number_format($diff_1).'</font>'; }
				//

				//EXP - With minigames $new_info[2] isn't set so lets skip that
				if(isset($new_info[2])){
					if($diff_2 == 0 && $new_info[2] != '-1'){ $pt3 = number_format($new_info[2]); }
					else if($new_info[2] == '-1'){ $pt3 = $new_info[2]; }
					else if($diff_2 > 0){ $pt3 = number_format($new_info[2]).' <font style="color:green;">&uarr; +'.number_format(abs($diff_2)).'</font>'; }
					else{ $pt3= number_format($new_info[0]).' <font style="color:red;">&darr; -'.number_format($diff_2).'</font>'; }
				}
				//
				
				if(isset($new_info[2])){ $out[$i] = $pt1.'@'.$pt2.'@'.$pt3; } 
				else{ $out[$i] = $pt1.'@'.$pt2; } //With minigames $new_info[2] isn't set so lets skip that
				$i++;
			}
		}
		return $out;
	}
	
	public function ready_out($old, $new){
		$data = $this->compare($old, $new); //compare the info
		$out = '';
		$i = 0;
		foreach ($data as $parsed){
			$parsed = explode("@", $parsed);
			if($i == 0){
				$out .= '<table><tr><th></th><th>Skills</th><th>Rank</th><th>Level</th><th>XP</th></tr>';
			}
			else if($i == 26){
				$out .= '</table><table><tr><th></th><th colspan="2">Game</th><th colspan="2">Rank</th><th>Score</th></tr>';
			}
			if($parsed[0] == '-1'){
				if(isset($parsed[2])){ //exp is set so this is a skill
					$out .= '<tr><td align="center"><img src="'.$this->image_links[$i].'"/></td><td>'.$this->skill_names[$i].'</td><td colspan="3" align="right">Not Ranked</td></tr>';
					$i++;
					continue;
				}
				else{ //exp isn't set so this is a minigame
					$out .= '<tr><td align="center"><img src="'.$this->image_links[$i].'"/></td><td colspan="2">'.$this->skill_names[$i].'</td><td colspan="3" align="right">Not Ranked</td></tr>';
					$i++;
					continue;
				}
			}
			if(isset($parsed[2])){ //exp is set so this is a skill
				$out .= '<tr><td><img src="'.$this->image_links[$i].'"/></td><td>'.$this->skill_names[$i].'</td><td>'.$parsed[0].'</td><td>'.$parsed[1].'</td><td>'.$parsed[2].'</td></tr>';
			}
			else{ //exp isn't set so this is a minigame
				$out .= '<tr><td align="center"><img src="'.$this->image_links[$i].'"/></td><td colspan="2">'.$this->skill_names[$i].'</td><td colspan="2">'.$parsed[0].'</td><td>'.$parsed[1].'</td></tr>';
			}
			$i++;
		}
		return $out;
	}
}

$rs = new rs();
$user_info = $rs->grab_new_info($user); //grab hs info from RuneScape
if($user_info){ //returned 200 - valid user
	$old_info = $rs->check_vdb_user($user_info);
	if(!$old_info){ //user isn't in the db so he doesn't have any old info, so lets set that
		$old_info['player_name'] = $rs->username;
		$old_info['data'] = $user_info;
		$old_info['last_updated'] = time();
	}
	//explode old and new and compare
	$user_info = explode ("\n", $user_info);
	$old_data = explode ("\n", $old_info['data']);
	$output = $rs->ready_out($user_info, $old_data);
}
else{ //something with the curl
	die($user." not a valid RuneScape user");
}
?>
<!DOCTYPE html>
<html>
    <head>
        <title>RSHiScores - #<?=$rs->username;?></title>
        <meta name="robots" content="noindex, nofollow, noarchive, nosnippet, noodp" /> <!-- I fucking hate robots... -->
        <meta name="description" content="RuneScape HiScore Grabber" />
        <meta charset="UTF-8" />
        <style type="text/css">
            html { background-color: #000;color: #777;font-family: sans-serif; font-size: 1.1em;padding: 1em 2em; }
            div { float: right;text-align: right; }
			font{font-weight:bold;}
        </style>
    </head>
    <body>
		<h1><?=$rs->username;?></h1>
		<h5><?=date("j-F-Y g:i:s A", $old_info['last_updated']);?></h5>
		<?=$output;?>
    </body>
</html>