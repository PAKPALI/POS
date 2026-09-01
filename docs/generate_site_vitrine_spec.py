from pathlib import Path
from reportlab.lib import colors
from reportlab.lib.enums import TA_CENTER, TA_LEFT
from reportlab.lib.pagesizes import A4, landscape
from reportlab.lib.styles import ParagraphStyle, getSampleStyleSheet
from reportlab.lib.units import mm
from reportlab.pdfbase import pdfmetrics
from reportlab.pdfbase.ttfonts import TTFont
from reportlab.platypus import (
    BaseDocTemplate, PageTemplate, Frame, Paragraph, Spacer, Table, TableStyle,
    PageBreak, KeepTogether, Image, Flowable, NextPageTemplate
)
from reportlab.pdfgen import canvas
from reportlab.lib.utils import ImageReader

ROOT = Path(r"C:\Users\didie\Documents\Codex\2026-09-01\lis-uniquement-docs-freebuff-handoff-md")
OUT = ROOT / "outputs" / "CAHIER_DES_CHARGES_SITE_VITRINE_POS_SAAS_AFRIQUE.pdf"
ASSETS = Path(r"C:\POS\docs\assets\design-system")
OUT.parent.mkdir(parents=True, exist_ok=True)

font_regular = Path(r"C:\Windows\Fonts\segoeui.ttf")
font_semibold = Path(r"C:\Windows\Fonts\seguisb.ttf")
font_bold = Path(r"C:\Windows\Fonts\segoeuib.ttf")
pdfmetrics.registerFont(TTFont("UI", str(font_regular)))
pdfmetrics.registerFont(TTFont("UI-Semibold", str(font_semibold)))
pdfmetrics.registerFont(TTFont("UI-Bold", str(font_bold)))

PAGE_W, PAGE_H = A4
BG = colors.HexColor("#07111F")
PANEL = colors.HexColor("#101C2D")
PANEL_2 = colors.HexColor("#15243A")
TEXT = colors.HexColor("#F3F6FB")
MUTED = colors.HexColor("#A9B5C8")
ACCENT = colors.HexColor("#FF9F43")
ACCENT_SOFT = colors.HexColor("#3B2A20")
TEAL = colors.HexColor("#20BFA9")
BORDER = colors.HexColor("#2A3A52")
DANGER = colors.HexColor("#FF626E")

styles = getSampleStyleSheet()
styles.add(ParagraphStyle(name="CoverKicker", fontName="UI-Bold", fontSize=9, leading=12, textColor=ACCENT, tracking=1.5, spaceAfter=8))
styles.add(ParagraphStyle(name="CoverTitle", fontName="UI-Bold", fontSize=30, leading=34, textColor=TEXT, spaceAfter=14))
styles.add(ParagraphStyle(name="CoverSub", fontName="UI", fontSize=12, leading=18, textColor=MUTED, spaceAfter=18))
styles.add(ParagraphStyle(name="H1x", fontName="UI-Bold", fontSize=20, leading=25, textColor=TEXT, spaceAfter=10))
styles.add(ParagraphStyle(name="H2x", fontName="UI-Bold", fontSize=13, leading=17, textColor=TEXT, spaceBefore=9, spaceAfter=6))
styles.add(ParagraphStyle(name="H3x", fontName="UI-Semibold", fontSize=10, leading=14, textColor=ACCENT, spaceBefore=7, spaceAfter=4))
styles.add(ParagraphStyle(name="Bodyx", fontName="UI", fontSize=8.5, leading=12.2, textColor=MUTED, spaceAfter=5))
styles.add(ParagraphStyle(name="BodyStrong", fontName="UI-Semibold", fontSize=8.5, leading=12.2, textColor=TEXT, spaceAfter=5))
styles.add(ParagraphStyle(name="Smallx", fontName="UI", fontSize=7.1, leading=9.5, textColor=MUTED))
styles.add(ParagraphStyle(name="TableHead", fontName="UI-Bold", fontSize=7.1, leading=9, textColor=TEXT))
styles.add(ParagraphStyle(name="TableCell", fontName="UI", fontSize=6.8, leading=9, textColor=MUTED))
styles.add(ParagraphStyle(name="Callout", fontName="UI-Semibold", fontSize=9, leading=13, textColor=TEXT))
styles.add(ParagraphStyle(name="Quote", fontName="UI-Bold", fontSize=15, leading=20, textColor=TEXT, alignment=TA_CENTER))

def P(text, style="Bodyx"):
    return Paragraph(text, styles[style])

def bullets(items):
    out = []
    for item in items:
        out.append(Paragraph(f"<font color='#FF9F43'>•</font> {item}", styles["Bodyx"]))
    return out

class RoundedPanel(Flowable):
    def __init__(self, width, height, title, body, accent=ACCENT):
        super().__init__(); self.width=width; self.height=height; self.title=title; self.body=body; self.accent=accent
    def draw(self):
        c=self.canv; c.saveState(); c.setFillColor(PANEL); c.setStrokeColor(BORDER); c.roundRect(0,0,self.width,self.height,10,fill=1,stroke=1)
        c.setFillColor(self.accent); c.roundRect(9,self.height-25,26,4,2,fill=1,stroke=0)
        c.setFont("UI-Bold",9); c.setFillColor(TEXT); c.drawString(10,self.height-38,self.title)
        tx=c.beginText(10,self.height-52); tx.setFont("UI",7.2); tx.setLeading(10); tx.setFillColor(MUTED)
        import textwrap
        for line in textwrap.wrap(self.body, 42): tx.textLine(line)
        c.drawText(tx); c.restoreState()

def page_bg(c, doc):
    c.saveState(); c.setFillColor(BG); c.rect(0,0,PAGE_W,PAGE_H,fill=1,stroke=0)
    c.setFillColor(colors.Color(1,.624,.263,alpha=.05)); c.circle(PAGE_W-25*mm,PAGE_H-25*mm,45*mm,fill=1,stroke=0)
    c.setStrokeColor(BORDER); c.line(18*mm,16*mm,PAGE_W-18*mm,16*mm)
    c.setFont("UI",7); c.setFillColor(MUTED); c.drawString(18*mm,10*mm,"CAHIER DES CHARGES - SITE VITRINE POS SAAS AFRIQUE")
    c.drawRightString(PAGE_W-18*mm,10*mm,f"{doc.page:02d}")
    c.restoreState()

def cover_bg(c, doc):
    c.saveState(); c.setFillColor(BG); c.rect(0,0,PAGE_W,PAGE_H,fill=1,stroke=0)
    c.setFillColor(colors.HexColor("#10243A")); c.circle(PAGE_W-20*mm,PAGE_H-32*mm,70*mm,fill=1,stroke=0)
    c.setFillColor(colors.HexColor("#332319")); c.circle(12*mm,28*mm,58*mm,fill=1,stroke=0)
    c.setStrokeColor(BORDER); c.roundRect(16*mm,18*mm,PAGE_W-32*mm,PAGE_H-36*mm,14,fill=0,stroke=1)
    c.restoreState()

doc = BaseDocTemplate(str(OUT), pagesize=A4, leftMargin=18*mm, rightMargin=18*mm, topMargin=20*mm, bottomMargin=22*mm,
                      title="Cahier des charges - Site vitrine POS SaaS Afrique", author="Codex")
frame = Frame(doc.leftMargin, doc.bottomMargin, doc.width, doc.height, id="normal")
doc.addPageTemplates([PageTemplate(id="cover", frames=frame, onPage=cover_bg), PageTemplate(id="body", frames=frame, onPage=page_bg)])

story=[]
story += [Spacer(1,42*mm), P("SITE VITRINE • PRODUIT • ACQUISITION", "CoverKicker"),
          P("Cahier des charges<br/>du site vitrine<br/>POS SaaS Afrique", "CoverTitle"),
          P("Une présence commerciale premium, vivante et crédible pour transformer la gestion quotidienne des commerces africains - avec la facture envoyée par SMS et WhatsApp comme promesse distinctive.", "CoverSub")]
cover_cards = Table([[RoundedPanel(52*mm,32*mm,"PROMESSE","Vendez. Encaissez. Envoyez la facture sur le téléphone du client."), RoundedPanel(52*mm,32*mm,"MARCHÉ","Commerçants, boutiques, PME et réseaux multi-activités en Afrique.",TEAL), RoundedPanel(52*mm,32*mm,"CONVERSION","Essai gratuit 14 jours, inscription directe et connexion vers l'application.",ACCENT)]], colWidths=[55*mm]*3)
cover_cards.setStyle(TableStyle([('VALIGN',(0,0),(-1,-1),'TOP'),('LEFTPADDING',(0,0),(-1,-1),0),('RIGHTPADDING',(0,0),(-1,-1),3*mm)]))
story += [cover_cards, Spacer(1,17*mm), P("Version 1.0 • 1er septembre 2026", "Smallx"), NextPageTemplate("body"), PageBreak()]

def section(title, intro=None):
    story.append(P(title.upper(), "CoverKicker")); story.append(P(title, "H1x"))
    if intro: story.append(P(intro, "BodyStrong"))

def callout(text, color=ACCENT):
    t=Table([[P(text,"Callout")]], colWidths=[doc.width])
    t.setStyle(TableStyle([('BACKGROUND',(0,0),(-1,-1),PANEL_2),('BOX',(0,0),(-1,-1),.8,color),('LEFTPADDING',(0,0),(-1,-1),11),('RIGHTPADDING',(0,0),(-1,-1),11),('TOPPADDING',(0,0),(-1,-1),9),('BOTTOMPADDING',(0,0),(-1,-1),9)])); story.append(t); story.append(Spacer(1,6))

section("1. Vision et ambition", "Le site doit être le vendeur silencieux de la plateforme : il explique le produit en quelques secondes, rassure sans exagérer et conduit naturellement à l'essai ou à la connexion.")
callout("Promesse centrale : « La vente ne s'arrête plus au ticket. Encaissez et envoyez immédiatement une facture claire par SMS ou WhatsApp. »")
story += bullets(["Positionner la solution comme un outil professionnel conçu pour les réalités commerciales africaines.","Faire comprendre le bénéfice avant de détailler la technologie.","Conserver l'identité du nouveau template : verre fonctionnel, surfaces sombres premium, accent orange personnalisable, profondeur douce.","Éviter les clichés visuels, les slogans génériques et les images manifestement artificielles.","Présenter les abonnements comme une offre prévisionnelle tant que leur implémentation n'est pas activée."])
story += [P("Objectifs mesurables","H2x")]
data=[[P("Objectif","TableHead"),P("Indicateur recommandé","TableHead"),P("Cible initiale","TableHead")],
      [P("Acquisition","TableCell"),P("Visiteurs cliquant sur Essayer gratuitement","TableCell"),P("≥ 6 %","TableCell")],
      [P("Activation","TableCell"),P("Inscriptions terminées après clic CTA","TableCell"),P("≥ 35 %","TableCell")],
      [P("Compréhension","TableCell"),P("Visiteurs atteignant Fonctionnalités ou Tarifs","TableCell"),P("≥ 45 %","TableCell")],
      [P("Performance","TableCell"),P("Lighthouse mobile / LCP","TableCell"),P("≥ 85 / < 2,5 s","TableCell")]]
t=Table(data,colWidths=[42*mm,85*mm,38*mm],repeatRows=1); t.setStyle(TableStyle([('BACKGROUND',(0,0),(-1,0),PANEL_2),('GRID',(0,0),(-1,-1),.5,BORDER),('VALIGN',(0,0),(-1,-1),'TOP'),('LEFTPADDING',(0,0),(-1,-1),7),('RIGHTPADDING',(0,0),(-1,-1),7),('TOPPADDING',(0,0),(-1,-1),7),('BOTTOMPADDING',(0,0),(-1,-1),7)])); story += [t,PageBreak()]

section("2. Positionnement de marque", "Une marque utile, africaine par son contexte et universelle par sa qualité d'exécution.")
story += [P("Territoire de marque","H2x")]
cards=Table([[RoundedPanel(52*mm,34*mm,"FIABLE","Données isolées par entreprise, permissions, transactions protégées et historique conservé.",TEAL),RoundedPanel(52*mm,34*mm,"PROCHE","Vocabulaire métier simple, prix lisibles en FCFA et parcours adaptés au mobile.",ACCENT),RoundedPanel(52*mm,34*mm,"AMBITIEUX","Du kiosque à un réseau de boutiques, avec plusieurs entreprises dans un même compte.",colors.HexColor('#8B7CFF'))]],colWidths=[55*mm]*3)
cards.setStyle(TableStyle([('LEFTPADDING',(0,0),(-1,-1),0),('RIGHTPADDING',(0,0),(-1,-1),3*mm)])); story += [cards,Spacer(1,9)]
story += [P("Piliers éditoriaux","H2x")]
story += bullets(["Parler de temps gagné, de contrôle et de service client ; éviter le jargon SaaS.","Employer des preuves concrètes : stock mis à jour, caisse alimentée, facture envoyée, boutique synchronisée.","Ne jamais promettre une couverture pays, fiscale ou télécom non validée.","Présenter la plateforme comme utilisable dans de nombreux pays africains, avec paramétrage pays/devise et validation locale avant commercialisation.","Ton : confiant, direct, chaleureux, sans surenchère ni accumulation d'emojis."])
story += [P("Messages proposés","H2x")]
for q in ["Chaque vente devient une relation client.","Votre boutique, votre stock et votre caisse avancent ensemble.","Travaillez depuis le comptoir ou votre téléphone, même avec une petite équipe."]:
    story += [P(f"« {q} »","Quote"),Spacer(1,5)]
story.append(PageBreak())

section("3. Publics cibles et scénarios", "Le site doit adapter ses preuves aux niveaux de maturité plutôt que présenter une longue liste identique à tout le monde.")
personas=[
    ("Commerçant individuel","Kiosque, artisan, salon, petite boutique","Vendre vite, suivre le stock, essayer sans risque","Essai 14 jours"),
    ("Boutique structurée","Mode, cosmétique, alimentation, quincaillerie","Clients, fournisseurs, E-commerce, trois accès","Bronze"),
    ("PME multi-activité","Grossiste ou entrepreneur avec plusieurs activités","Deux compagnies, équipe et reporting central","Argent"),
    ("Réseau de boutiques","Groupe avec responsables et nombreux produits","Permissions, cinq compagnies, quinze utilisateurs","Gold"),
]
data=[[P("Profil","TableHead"),P("Contexte","TableHead"),P("Preuve à montrer","TableHead"),P("Plan repère","TableHead")]]+[[P(a,"TableCell"),P(b,"TableCell"),P(c,"TableCell"),P(d,"TableCell")] for a,b,c,d in personas]
t=Table(data,colWidths=[34*mm,43*mm,67*mm,24*mm],repeatRows=1); t.setStyle(TableStyle([('BACKGROUND',(0,0),(-1,0),PANEL_2),('GRID',(0,0),(-1,-1),.5,BORDER),('VALIGN',(0,0),(-1,-1),'TOP'),('LEFTPADDING',(0,0),(-1,-1),6),('RIGHTPADDING',(0,0),(-1,-1),6),('TOPPADDING',(0,0),(-1,-1),7),('BOTTOMPADDING',(0,0),(-1,-1),7)])); story += [t,Spacer(1,9)]
story += [P("Parcours narratif principal","H2x")]
story += bullets(["Le visiteur reconnaît son problème : ventes dispersées, stock incertain, factures difficiles à transmettre.","Il découvre la promesse unique SMS/WhatsApp dans le hero et une démonstration animée courte.","Il voit comment POS, caisse, inventaire, clients et E-commerce fonctionnent ensemble.","Il consulte des preuves, la sécurité, les secteurs et les tarifs.","Il démarre l'essai gratuit ou se connecte sans rupture de contexte."])
story.append(PageBreak())

section("4. Proposition de valeur et fonctionnalités", "La facture mobile est la porte d'entrée. La profondeur du produit justifie ensuite l'abonnement.")
features=[
    ("Facture par SMS et WhatsApp","Après la vente, le reçu peut être envoyé directement au client selon ses coordonnées et les canaux autorisés."),
    ("POS et caisse intégrés","Vente, montant reçu, remise, monnaie, caisse principale et taxe restent dans un même flux."),
    ("Stock fiable","Entrées, sorties, seuils, fournisseurs, inventaires et diminution automatique lors d'une vente confirmée."),
    ("E-commerce connecté","Boutique publique par adresse unique, commande client puis conversion contrôlée en vente."),
    ("Clients et communication","Fichier clients, destinataires configurables, e-mail, SMS, WhatsApp et historique de livraison."),
    ("Équipe et permissions","Rôles par entreprise, invitations sécurisées et accès limités aux fonctions nécessaires."),
    ("Multi-compagnies","Plusieurs activités pilotées depuis un compte avec séparation stricte des données."),
    ("Rapports et exports","Tableaux de bord, historiques, marges selon permission, CSV, Excel et PDF avec limites sûres."),
    ("PWA mobile","Installation sur Android/iOS, panier persistant et expérience adaptée aux connexions mobiles."),
]
rows=[]
for i in range(0,len(features),3):
    row=[]
    for title,body in features[i:i+3]: row.append(RoundedPanel(52*mm,38*mm,title.upper(),body,TEAL if i%2 else ACCENT))
    while len(row)<3: row.append(Spacer(1,1))
    rows.append(row)
t=Table(rows,colWidths=[55*mm]*3,rowHeights=[41*mm]*3); t.setStyle(TableStyle([('VALIGN',(0,0),(-1,-1),'TOP'),('LEFTPADDING',(0,0),(-1,-1),0),('RIGHTPADDING',(0,0),(-1,-1),3*mm)])); story += [t,PageBreak()]

section("5. Architecture du site", "Une page d'accueil riche peut porter la conversion, accompagnée de pages dédiées pour le référencement, la confiance et les besoins complexes.")
architecture=[
    ("/","Accueil : promesse, démonstration, bénéfices, modules, preuves, tarifs, FAQ, CTA."),
    ("/fonctionnalites","Vue détaillée par domaine : ventes, stock, caisse, clients, équipe, communication, E-commerce."),
    ("/factures-sms-whatsapp","Page SEO et conversion consacrée au différenciateur principal."),
    ("/secteurs","Cas d'usage : boutique, alimentation, mode, salon, grossiste, réseau."),
    ("/tarifs","Essai et grille prévisionnelle, mensuel/annuel, limites, quotas et FAQ."),
    ("/securite","Isolation des entreprises, permissions, sauvegarde, paiements et confidentialité."),
    ("/aide","Guides courts, questions, contact et statut du service."),
    ("/connexion","Redirection directe vers l'interface de connexion de l'application."),
    ("/inscription","Redirection directe vers l'inscription et la création de la première entreprise."),
    ("/mentions-legales","Mentions, confidentialité, cookies, conditions et pays d'exploitation."),
]
data=[[P("URL","TableHead"),P("Rôle","TableHead")]]+[[P(a,"TableCell"),P(b,"TableCell")] for a,b in architecture]
t=Table(data,colWidths=[48*mm,120*mm],repeatRows=1); t.setStyle(TableStyle([('BACKGROUND',(0,0),(-1,0),PANEL_2),('GRID',(0,0),(-1,-1),.5,BORDER),('VALIGN',(0,0),(-1,-1),'TOP'),('LEFTPADDING',(0,0),(-1,-1),7),('RIGHTPADDING',(0,0),(-1,-1),7),('TOPPADDING',(0,0),(-1,-1),6),('BOTTOMPADDING',(0,0),(-1,-1),6)])); story += [t]
story += [P("Navigation globale","H2x")]+bullets(["Logo à gauche ; Fonctionnalités, Solutions, Tarifs, Sécurité et Aide au centre.","Actions persistantes : Se connecter en secondaire et Essayer gratuitement en primaire.","Mobile : barre compacte et menu plein écran ; CTA Essayer toujours atteignable sans masquer le contenu.","Le domaine racine affiche le site. L'application conserve ses routes d'authentification et son espace connecté."])
story.append(PageBreak())

section("6. Page d'accueil - storyboard", "Chaque section doit apporter une nouvelle preuve. Aucun empilement décoratif sans fonction commerciale.")
home=[
    ("01 Hero","Promesse SMS/WhatsApp, sous-promesse globale, deux CTA, aperçu réel du POS et marqueurs de confiance."),
    ("02 Démonstration","Animation en 4 étapes : produit ajouté, paiement confirmé, reçu généré, téléphone du client notifié."),
    ("03 Résultats","Vendre plus vite, réduire les erreurs, fidéliser, connaître le stock et suivre la caisse."),
    ("04 Modules","Navigation interactive entre Vente, Catalogue, Inventaire, Clients, Comptabilité, Équipe, Communication et E-commerce."),
    ("05 Multi-support","Vue desktop, tablette et téléphone ; PWA installable ; panier persistant."),
    ("06 E-commerce","De la boutique publique à la conversion en vente sans diminuer prématurément le stock."),
    ("07 Confiance","Isolation par entreprise, permissions, transactions atomiques, sauvegarde et paiements vérifiés."),
    ("08 Secteurs","Exemples africains crédibles, photos documentaires locales et bénéfices par métier."),
    ("09 Tarifs","Essai 14 jours, cartes comparables, Bronze recommandé, bascule mensuel/annuel."),
    ("10 Témoignages","Emplacements prévus ; ne publier que des témoignages réels, datés et autorisés."),
    ("11 FAQ","Internet, téléphone, factures, quotas, données, abonnement, changement de plan."),
    ("12 CTA final","Créer mon espace gratuitement ; rappel 14 jours et aucune carte si cette règle est validée."),
]
data=[[P("Bloc","TableHead"),P("Contenu et objectif","TableHead")]]+[[P(a,"TableCell"),P(b,"TableCell")] for a,b in home]
t=Table(data,colWidths=[35*mm,133*mm],repeatRows=1); t.setStyle(TableStyle([('BACKGROUND',(0,0),(-1,0),PANEL_2),('GRID',(0,0),(-1,-1),.5,BORDER),('VALIGN',(0,0),(-1,-1),'TOP'),('LEFTPADDING',(0,0),(-1,-1),7),('RIGHTPADDING',(0,0),(-1,-1),7),('TOPPADDING',(0,0),(-1,-1),5),('BOTTOMPADDING',(0,0),(-1,-1),5)])); story += [t,PageBreak()]

section("7. Hero et démonstration signature", "La première vue doit expliquer le produit avant le premier scroll.")
story += [P("Contenu recommandé","H2x")]
callout("Titre : « Encaissez ici. Votre client reçoit sa facture sur son téléphone. »")
story += bullets(["Sous-titre : « Un POS complet pour vendre, suivre le stock, piloter la caisse et envoyer les reçus par SMS ou WhatsApp. »","CTA primaire : Essayer gratuitement pendant 14 jours.","CTA secondaire : Voir la démonstration.","Lien discret : Déjà client ? Se connecter.","Réassurance : PWA mobile • Plusieurs entreprises • Données séparées • Exports."])
story += [P("Animation fonctionnelle","H2x")]
story += bullets(["Durée totale 5 à 7 secondes, déclenchée à l'entrée dans la section et non en boucle continue.","Étape 1 : un produit réel rejoint le panier ; étape 2 : total et monnaie ; étape 3 : reçu ; étape 4 : notification SMS/WhatsApp sur un téléphone stylisé.","Les textes restent lisibles sans animation ; une commande Lecture/Pause est disponible.","Avec prefers-reduced-motion, afficher les quatre états côte à côte sans trajectoire.","Ne jamais utiliser une vidéo lourde en arrière-plan."])
story += [P("Preuve honnête","H2x")]+bullets(["Mentionner que les messages consomment un quota et dépendent du canal configuré.","Ne pas afficher les logos WhatsApp ou opérateurs sans vérifier les règles de marque.","La démonstration doit utiliser des données fictives clairement identifiables et aucun numéro réel."])
story.append(PageBreak())

section("8. Direction artistique", "Le site étend le template SaaS au marketing, avec davantage de respiration et d'émotion mais les mêmes fondations.")
design=[("Fond","Canvas sombre premium, alternative claire, halos limités et aucun motif génératif omniprésent."),("Surfaces","Trois niveaux maximum ; verre renforcé pour navigation, hero et comparateurs."),("Accent","Orange actuel par défaut ; vert pour validation et communication ; rouge uniquement pour les erreurs."),("Typographie","Sans-serif lisible ; titres courts et expressifs ; chiffres tabulaires pour prix et données."),("Iconographie","SVG cohérents, traits simples, pictogrammes métier ; pas de mélange de bibliothèques."),("Photographie","Scènes réelles de commerce africain, gestes de vente, produits locaux et diversité sans mise en scène artificielle."),("Illustrations","Captures produit réelles encadrées, schémas simples et téléphone de facture ; éviter les personnages 3D génériques."),("Mouvement","Transform/opacity uniquement, 140-280 ms ; animations explicatives limitées et arrêtables.")]
data=[[P("Élément","TableHead"),P("Règle","TableHead")]]+[[P(a,"TableCell"),P(b,"TableCell")] for a,b in design]
t=Table(data,colWidths=[38*mm,130*mm],repeatRows=1); t.setStyle(TableStyle([('BACKGROUND',(0,0),(-1,0),PANEL_2),('GRID',(0,0),(-1,-1),.5,BORDER),('VALIGN',(0,0),(-1,-1),'TOP'),('LEFTPADDING',(0,0),(-1,-1),7),('RIGHTPADDING',(0,0),(-1,-1),7),('TOPPADDING',(0,0),(-1,-1),6),('BOTTOMPADDING',(0,0),(-1,-1),6)])); story += [t,Spacer(1,8)]
callout("Garde-fou : la qualité perçue vient d'abord de la cohérence des espacements, de la typographie, des états et du vocabulaire. Le flou et les halos ne remplacent jamais cette rigueur.",TEAL)
story.append(PageBreak())

section("9. Références visuelles produit", "Les visuels marketing doivent provenir en priorité de l'interface réelle, nettoyée et cadrée, afin que la promesse corresponde au produit.")
for filename,caption in [("pos-concept-v1.png","Référence POS : rapidité de vente, panier clair et hiérarchie métier."),("dashboard-concept-v1.png","Référence tableau de bord : indicateurs, activité et vision immédiate."),("company-theme-concept-v1.png","Référence personnalisation : identité de compagnie et thème utilisateur.")]:
    path=ASSETS/filename
    if path.exists():
        img=Image(str(path),width=92*mm,height=52*mm,kind='proportional');
        block=Table([[img,P(caption,"BodyStrong")]],colWidths=[98*mm,65*mm]); block.setStyle(TableStyle([('BACKGROUND',(0,0),(-1,-1),PANEL),('BOX',(0,0),(-1,-1),.6,BORDER),('VALIGN',(0,0),(-1,-1),'MIDDLE'),('LEFTPADDING',(0,0),(-1,-1),7),('RIGHTPADDING',(0,0),(-1,-1),7),('TOPPADDING',(0,0),(-1,-1),7),('BOTTOMPADDING',(0,0),(-1,-1),7)])); story += [block,Spacer(1,7)]
story.append(PageBreak())

section("10. Tarifs et abonnements prévisionnels", "La grille ci-dessous provient de la stratégie tarifaire datée du 31 août 2026. Elle doit être présentée comme prévue ou bientôt disponible tant que le moteur d'abonnement n'est pas en production.")
plans=[("Essai","0","14 jours","1 / 1 / 10","3 / 3","Toutes fonctions"),("Basic","2 500","mois","1 / 2 / 50","10 / 10","Sans fournisseurs ni E-commerce"),("Bronze","5 000","mois","1 / 3 / 150","20 / 20","Complet - recommandé"),("Argent","10 000","mois","2 / 5 / 500","50 / 50","Complet, multi-activité"),("Gold","20 000","mois","5 / 15 / 1 000","100 / 100","Capacité maximale")]
data=[[P("Plan","TableHead"),P("FCFA HT","TableHead"),P("Période","TableHead"),P("Comp./util./prod.","TableHead"),P("SMS/WA","TableHead"),P("Positionnement","TableHead")]]+[[P(x,"TableCell") for x in row] for row in plans]
t=Table(data,colWidths=[24*mm,25*mm,22*mm,34*mm,24*mm,39*mm],repeatRows=1); t.setStyle(TableStyle([('BACKGROUND',(0,0),(-1,0),PANEL_2),('BACKGROUND',(0,3),(-1,3),ACCENT_SOFT),('GRID',(0,0),(-1,-1),.5,BORDER),('VALIGN',(0,0),(-1,-1),'TOP'),('LEFTPADDING',(0,0),(-1,-1),5),('RIGHTPADDING',(0,0),(-1,-1),5),('TOPPADDING',(0,0),(-1,-1),7),('BOTTOMPADDING',(0,0),(-1,-1),7)])); story += [t,Spacer(1,8)]
story += [P("Règles d'affichage","H2x")]+bullets(["Bascule Mensuel/Annuel ; annuel = 11 mois facturés pour 12 mois d'accès.","Prix annuels : Basic 27 500, Bronze 55 000, Argent 110 000, Gold 220 000 FCFA HT.","Quotas annuels crédités à l'activation : 120/120, 240/240, 600/600 et 1 200/1 200.","Les crédits non consommés restent cumulés ; SMS et WhatsApp demeurent séparés ; recharges supplémentaires payantes.","Atteindre une limite ne supprime aucune donnée. Après expiration, lecture/export conservés, opérations suspendues et boutique désactivée.","Le bouton de souscription doit rester libellé Être informé ou Essayer gratuitement tant que le paiement d'abonnement n'est pas implémenté."])
story.append(PageBreak())

section("11. Contenus publicitaires et preuves", "La publicité doit être abondante mais structurée : chaque affirmation importante reçoit une preuve visuelle, fonctionnelle ou documentaire.")
proofs=[("Preuve produit","Captures réelles, mini-démo interactive et écrans cohérents avec l'application."),("Preuve métier","Scénarios : vente, facture mobile, inventaire, commande E-commerce, équipe."),("Preuve technique","PWA, isolation multi-tenant, permissions, ventes atomiques, jobs idempotents."),("Preuve performance","Benchmarks locaux à gros volume présentés avec contexte, sans garantie d'hébergement."),("Preuve sociale","Témoignages réels uniquement ; secteur, ville/pays et autorisation de publication."),("Preuve commerciale","Tarifs simples en FCFA, essai réel, limites transparentes et aucune suppression à l'expiration.")]
for title,body in proofs: story += [KeepTogether([P(title,"H3x"),P(body,"Bodyx")])]
story += [P("Campagnes et pages d'atterrissage","H2x")]+bullets(["/facture-whatsapp pour campagnes orientées fidélisation et preuve d'achat.","/gestion-stock pour commerces ayant des ruptures ou écarts fréquents.","/boutique-en-ligne pour vendre sur le web sans séparer le catalogue du POS.","/multi-boutiques pour groupes et entrepreneurs multi-activités.","Chaque campagne conserve le même design, un seul message principal et un CTA mesurable."])
story.append(PageBreak())

section("12. Expérience responsive et accessibilité", "Le site est d'abord conçu pour le téléphone, sans dégrader l'impact desktop.")
responsive=[["320-389 px","Une colonne, navigation plein écran, CTA largeur utile, tableaux en cartes ou scroll local."],["390-767 px","Hero compact, démonstration verticale, tarifs un plan à la fois avec comparaison accessible."],["768-1023 px","Deux colonnes sélectives, médias proportionnés, navigation tablette."],["1024-1279 px","Hero et démonstration côte à côte, menu compact, grilles 2-3 colonnes."],["≥ 1280 px","Large respiration, contenu plafonné, grilles 3-4 colonnes sans lignes trop longues."]]
data=[[P("Palier","TableHead"),P("Comportement","TableHead")]]+[[P(a,"TableCell"),P(b,"TableCell")] for a,b in responsive]
t=Table(data,colWidths=[35*mm,133*mm]); t.setStyle(TableStyle([('BACKGROUND',(0,0),(-1,0),PANEL_2),('GRID',(0,0),(-1,-1),.5,BORDER),('LEFTPADDING',(0,0),(-1,-1),7),('RIGHTPADDING',(0,0),(-1,-1),7),('TOPPADDING',(0,0),(-1,-1),7),('BOTTOMPADDING',(0,0),(-1,-1),7)])); story += [t,Spacer(1,8)]
story += bullets(["WCAG 2.1 AA visé ; focus visible, clavier complet, ordre logique et contrastes vérifiés.","Zones tactiles d'au moins 44 × 44 px et textes utilisables à 200 %.","Aucune information uniquement par la couleur ; textes alternatifs utiles et images décoratives ignorées.","prefers-reduced-motion respecté ; contrôle Pause sur toute animation longue.","Langue initiale française ; architecture préparée pour anglais et langues de marché sans texte intégré aux images."])
story.append(PageBreak())

section("13. Performance, SEO et mesure", "Un site spectaculaire mais lent détruirait la promesse d'efficacité du POS.")
story += [P("Budgets obligatoires","H2x")]+bullets(["Aucune vidéo d'arrière-plan ; visuel décoratif WebP/AVIF < 150 Ko.","CSS marketing mutualisé et composants locaux ; aucun nouveau framework CSS.","JavaScript différé, animations transform/opacity et maximum cinq surfaces floutées visibles.","Images dimensionnées, responsive, lazy-load hors hero ; hero préchargé avec parcimonie.","Lighthouse mobile : Performance ≥ 85, Accessibilité ≥ 90, Bonnes pratiques ≥ 90, SEO ≥ 90."])
story += [P("SEO","H2x")]+bullets(["Titres uniques et intentions locales : logiciel de caisse, POS Afrique, facture WhatsApp, gestion stock boutique.","Données structurées SoftwareApplication, Product/Offer lorsque les tarifs sont activés, FAQPage pour les questions visibles.","Open Graph et images sociales réelles ; sitemap, canonical, robots et redirections propres.","Pages rapides par secteur/pays seulement si le contenu est réellement localisé ; aucune duplication automatique.","Mentions sur fiscalité, factures et conformité validées pays par pays."])
story += [P("Analytics respectueux","H2x")]+bullets(["Événements : hero_try, demo_play, feature_open, pricing_toggle, plan_select, signup_start, signup_complete.","Mesurer la source, le pays approximatif et l'appareil sans collecter inutilement des données personnelles.","Bannière cookies uniquement si les outils choisis la rendent nécessaire ; consentement explicite pour marketing."])
story.append(PageBreak())

section("14. Intégration avec l'application", "Le site public et l'application doivent paraître appartenir au même produit tout en restant techniquement séparables.")
story += bullets(["Le domaine racine `/` sert le site vitrine.","Se connecter pointe vers la route de connexion existante ; S'inscrire vers le parcours de création SaaS.","Après authentification, aucune navigation marketing ne doit interrompre le choix de compagnie ou les permissions.","Les tokens design sont partagés ; les CSS marketing ne doivent pas charger les scripts métier du POS.","Les captures et nombres du site ne lisent pas de données client réelles.","Les tarifs proviennent à terme d'une source serveur unique ; aucun montant métier dupliqué dans plusieurs fichiers.","Prévoir une bannière d'annonce pilotable depuis l'administration, sans HTML arbitraire.","L'état du plan d'abonnement doit être activé seulement après implémentation de l'EntitlementService et validation du paiement serveur."])
story += [P("Sécurité publique","H2x")]+bullets(["Throttling sur inscription, connexion, contact et endpoints de démonstration.","CSRF sur formulaires, validation serveur et honeypot ou anti-abus accessible.","CSP, HSTS en production, cookies sécurisés, aucune clé API dans le navigateur.","Pas de données de carte : paiement délégué à un prestataire et confirmation vérifiée côté serveur."])
story.append(PageBreak())

section("15. Composants à produire", "La bibliothèque marketing reprend les règles du design system et interdit les variantes improvisées.")
components=["MarketingHeader","HeroProductDemo","TrustStrip","FeatureTabs","FeatureCard","PhoneReceiptDemo","UseCaseCard","ScreenshotFrame","MetricProof","PricingToggle","PricingCard","ComparisonTable","TestimonialCard","FaqAccordion","FinalCta","MarketingFooter","CookieNotice","AnnouncementBar","LanguageSwitcher","ReducedMotionControl"]
rows=[]
for i in range(0,len(components),3): rows.append([P(x,"TableCell") for x in components[i:i+3]])
t=Table(rows,colWidths=[56*mm]*3); t.setStyle(TableStyle([('BACKGROUND',(0,0),(-1,-1),PANEL),('GRID',(0,0),(-1,-1),.5,BORDER),('LEFTPADDING',(0,0),(-1,-1),8),('RIGHTPADDING',(0,0),(-1,-1),8),('TOPPADDING',(0,0),(-1,-1),8),('BOTTOMPADDING',(0,0),(-1,-1),8)])); story += [t,Spacer(1,8)]
story += [P("États obligatoires","H2x")]+bullets(["Normal, hover, focus, actif et désactivé.","Chargement, vide, succès, erreur et hors ligne pour tout contenu distant.","Variantes sombre, claire, système et mouvement réduit.","Contenus longs, montants à six chiffres, noms de produits longs et traduction future."])
story.append(PageBreak())

section("16. Plan de réalisation", "Le lancement se fait en lots vérifiables, sans attendre que toutes les campagnes secondaires soient prêtes.")
phases=[("Lot 0 - validation","Nom, logo, promesse, pays initiaux, disponibilité de l'essai et statut exact des abonnements."),("Lot 1 - fondations","Tokens, layout marketing, navigation, footer, SEO technique, analytics et composants."),("Lot 2 - accueil MVP","Hero, démo facture, fonctionnalités, sécurité, tarifs prévisionnels, FAQ et CTA."),("Lot 3 - pages de preuve","Fonctionnalités, SMS/WhatsApp, secteurs, sécurité, tarifs et aide."),("Lot 4 - contenus réels","Captures finales, photographie, témoignages autorisés, traductions et mentions légales."),("Lot 5 - optimisation","Tests utilisateurs, A/B tests raisonnés, performance, SEO, accessibilité et conversion."),("Lot 6 - abonnement réel","Connexion à la source tarifaire et au checkout seulement lorsque l'implémentation est validée.")]
data=[[P("Phase","TableHead"),P("Résultat","TableHead")]]+[[P(a,"TableCell"),P(b,"TableCell")] for a,b in phases]
t=Table(data,colWidths=[43*mm,125*mm]); t.setStyle(TableStyle([('BACKGROUND',(0,0),(-1,0),PANEL_2),('GRID',(0,0),(-1,-1),.5,BORDER),('VALIGN',(0,0),(-1,-1),'TOP'),('LEFTPADDING',(0,0),(-1,-1),7),('RIGHTPADDING',(0,0),(-1,-1),7),('TOPPADDING',(0,0),(-1,-1),7),('BOTTOMPADDING',(0,0),(-1,-1),7)])); story += [t,Spacer(1,8)]
story += [P("Livrables","H2x")]+bullets(["Wireframes mobile et desktop ; prototype animé du hero ; maquettes haute fidélité.","Bibliothèque de composants ; site intégré ; contenus éditoriaux ; médias optimisés.","Plan de tags analytics ; SEO ; accessibilité ; performance ; sécurité ; matrice responsive.","Guide d'administration des contenus et procédure de publication."])
story.append(PageBreak())

section("17. Recette et critères d'acceptation", "Le site n'est terminé que lorsque chaque critère est démontré sur une version de préproduction.")
criteria=["La promesse facture SMS/WhatsApp est comprise sans scroller.","Connexion et inscription ouvrent les bonnes interfaces de l'application.","Aucun tarif futur n'est présenté comme déjà disponible.","Les prix, limites et quotas affichés correspondent à la source validée.","Le site fonctionne de 320 px aux grands écrans, portrait et paysage.","Clavier, focus, lecteur d'écran de base et mouvement réduit sont validés.","Aucun débordement global, texte coupé ou CTA masqué.","Images nettes, dimensionnées et sans contenu client réel.","Lighthouse atteint les cibles définies sur les pages publiques majeures.","Aucune erreur console, double soumission ou succès avant réponse serveur.","Les pages indexables ont title, description, canonical et données structurées valides.","Les formulaires sont protégés et les mentions légales validées.","Chrome Android, Safari iPhone, Firefox et Edge sont testés.","Les analytics enregistrent les conversions sans fuite de données sensibles.","Une procédure de retour arrière et une sauvegarde précèdent la mise en production."]
for i,item in enumerate(criteria,1): story.append(P(f"<font color='#FF9F43'><b>{i:02d}</b></font> &nbsp;{item}","Bodyx"))
story.append(PageBreak())

section("18. Décisions à valider avant développement", "Ces choix modifient le contenu, les CTA ou la conformité. Ils ne doivent pas être devinés par l'équipe de réalisation.")
decisions=["Nom commercial définitif et logo officiel.","Pays du lancement initial, devise et langues prioritaires.","Essai sans carte bancaire ou avec moyen de paiement.","Date réelle d'activation des abonnements et formulation avant cette date.","Capacité à envoyer effectivement WhatsApp dans chaque pays ciblé.","Politique de recharge, expiration et remboursement des quotas.","Canal de support : téléphone, WhatsApp, e-mail ou centre d'aide.","Témoignages et entreprises pilotes autorisés à être cités.","Prestataire analytics, politique cookies et durées de conservation.","Mentions légales, fiscalité, facturation et protection des données par pays."]
story += bullets(decisions)
callout("Recommandation de lancement : publier d'abord un site rapide et crédible avec des preuves produit réelles. Ajouter ensuite les témoignages, pages pays et campagnes au rythme des validations terrain.",TEAL)
story += [Spacer(1,10),P("Sources internes consultées","H2x"),P("Cahier des charges Design System UI/UX v1.6 ; Stratégie tarifaire des abonnements POS Afrique v2.1 ; Rapport global SaaS ; journal de passation Freebuff ; documentation d'architecture SaaS. Les tarifs sont une intention commerciale non encore implémentée.","Smallx")]

doc.build(story)
print(OUT)
